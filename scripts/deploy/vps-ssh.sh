#!/usr/bin/env bash

set -Eeuo pipefail

readonly SCRIPT_NAME="$(basename "$0")"

fail() {
    printf 'deploy: %s\n' "$1" >&2
    exit 1
}

usage() {
    printf 'Usage: %s IMAGE_TAR RELEASE_SHA SSH_TARGET\n' "$SCRIPT_NAME" >&2
    printf 'SSH_TARGET format: ssh://user@host[:port]/absolute/remote/path\n' >&2
    exit 2
}

[[ $# -eq 3 ]] || usage

artifact=$1
release_ref=$2
target=$3

[[ -f "$artifact" ]] || fail "artifact does not exist: $artifact"
[[ "$release_ref" =~ ^[0-9a-f]{40}$ ]] || fail 'release must be a full 40-character lowercase commit SHA'
[[ -n "${DEPLOY_CREDENTIAL:-}" ]] || fail 'DEPLOY_CREDENTIAL is required'
[[ -n "${DEPLOY_KNOWN_HOSTS:-}" ]] || fail 'DEPLOY_KNOWN_HOSTS is required'

if [[ ! "$target" =~ ^ssh://([a-zA-Z0-9._-]+)@([a-zA-Z0-9.-]+)(:([0-9]{1,5}))?(/([a-zA-Z0-9._/-]+))$ ]]; then
    fail 'target must be ssh://user@host[:port]/absolute/path'
fi

ssh_user=${BASH_REMATCH[1]}
ssh_host=${BASH_REMATCH[2]}
ssh_port=${BASH_REMATCH[4]:-22}
remote_root=${BASH_REMATCH[5]}
ssh_destination="${ssh_user}@${ssh_host}"

[[ "$remote_root" != *'..'* ]] || fail 'remote path cannot contain ..'
(( ssh_port >= 1 && ssh_port <= 65535 )) || fail 'SSH port is outside the valid range'

ssh_bin=${SSH_BIN:-ssh}
scp_bin=${SCP_BIN:-scp}
sha256sum_bin=${SHA256SUM_BIN:-sha256sum}
for command in "$ssh_bin" "$scp_bin" "$sha256sum_bin" mktemp chmod rm cp; do
    command -v "$command" >/dev/null 2>&1 || fail "required command is unavailable: $command"
done

temporary_directory=$(mktemp -d)
cleanup() {
    rm -rf "$temporary_directory"
}
trap cleanup EXIT

identity_file="$temporary_directory/deploy-key"
known_hosts_file="$temporary_directory/known-hosts"
checksum_file="$temporary_directory/artifact.sha256"
compose_file="$temporary_directory/compose.yaml"
printf '%s\n' "$DEPLOY_CREDENTIAL" > "$identity_file"
printf '%s\n' "$DEPLOY_KNOWN_HOSTS" > "$known_hosts_file"
chmod 600 "$identity_file" "$known_hosts_file"
cp compose.yaml "$compose_file"

artifact_checksum=$("$sha256sum_bin" "$artifact" | awk '{print $1}')
printf '%s  artifact.tar\n' "$artifact_checksum" > "$checksum_file"

ssh_options=(-p "$ssh_port" -i "$identity_file" -o BatchMode=yes -o IdentitiesOnly=yes -o StrictHostKeyChecking=yes -o UserKnownHostsFile="$known_hosts_file")
remote_incoming="$remote_root/incoming/$release_ref"

"$ssh_bin" "${ssh_options[@]}" "$ssh_destination" mkdir -p -- "$remote_incoming"
"$scp_bin" -P "$ssh_port" -i "$identity_file" -o BatchMode=yes -o IdentitiesOnly=yes -o StrictHostKeyChecking=yes -o UserKnownHostsFile="$known_hosts_file" \
    "$artifact" "$checksum_file" "$compose_file" "$ssh_destination:$remote_incoming/"

"$ssh_bin" "${ssh_options[@]}" "$ssh_destination" bash -s -- "$release_ref" "$remote_root" "$artifact_checksum" <<'REMOTE_SCRIPT'
set -Eeuo pipefail

release_ref=$1
remote_root=$2
expected_checksum=$3
incoming="$remote_root/incoming/$release_ref"
release="$remote_root/releases/$release_ref"
config="$remote_root/config/production.env"
project=portfolio

fail() {
    printf 'remote deploy: %s\n' "$1" >&2
    exit 1
}

[[ -r "$config" ]] || fail "missing external configuration: $config"
grep -Eq '^APP_ENV=production$' "$config" || fail 'APP_ENV must be production'
grep -Eq '^APP_DEBUG=false$' "$config" || fail 'APP_DEBUG must be false'
grep -Eq '^APP_KEY=.+$' "$config" || fail 'APP_KEY is missing'
grep -Eq '^APP_URL=https://[^[:space:]]+$' "$config" || fail 'APP_URL must be an HTTPS URL'
grep -Eq '^PORTFOLIO_PUBLIC_URL=https://[^[:space:]]+$' "$config" || fail 'PORTFOLIO_PUBLIC_URL must be an HTTPS URL'

command -v docker >/dev/null 2>&1 || fail 'docker is unavailable'
docker compose version >/dev/null 2>&1 || fail 'docker compose is unavailable'
command -v flock >/dev/null 2>&1 || fail 'flock is unavailable'
command -v sha256sum >/dev/null 2>&1 || fail 'sha256sum is unavailable'
command -v curl >/dev/null 2>&1 || fail 'curl is unavailable'

mkdir -p "$remote_root"/{releases,incoming,backups,config,run}
exec 9>"$remote_root/run/deploy.lock"
flock -n 9 || fail 'another deployment is already running'

[[ "$expected_checksum" =~ ^[0-9a-f]{64}$ ]] || fail 'invalid expected artifact checksum'
[[ -f "$incoming/artifact.tar" && -f "$incoming/artifact.sha256" && -f "$incoming/compose.yaml" ]] || fail 'incomplete uploaded release'
printf '%s  artifact.tar\n' "$expected_checksum" | cmp -s - "$incoming/artifact.sha256" || fail 'uploaded checksum manifest differs from local checksum'
(cd "$incoming" && sha256sum -c artifact.sha256) || fail 'artifact checksum verification failed'

old_current=$(readlink -f "$remote_root/current" 2>/dev/null || true)
old_release_ref=''
if [[ -n "$old_current" && "$old_current" == "$remote_root/releases/"* ]]; then
    old_release_ref=${old_current##*/}
fi

rm -rf "$release"
mv "$incoming" "$release"
docker load < "$release/artifact.tar" >/dev/null
docker image inspect "portfolio-app:$release_ref" >/dev/null || fail 'PHP-FPM image was not loaded'
docker image inspect "portfolio-web:$release_ref" >/dev/null || fail 'Nginx image was not loaded'

compose=(docker compose --project-name "$project" --env-file "$config" -f "$release/compose.yaml")
export PORTFOLIO_APP_IMAGE="portfolio-app:$release_ref"
export PORTFOLIO_WEB_IMAGE="portfolio-web:$release_ref"

rollback() {
    trap - ERR
    printf 'remote deploy: deployment failed; restoring previous release\n' >&2
    if [[ -n "$old_release_ref" && -f "$old_current/compose.yaml" ]]; then
        old_compose=(docker compose --project-name "$project" --env-file "$config" -f "$old_current/compose.yaml")
        export PORTFOLIO_APP_IMAGE="portfolio-app:$old_release_ref"
        export PORTFOLIO_WEB_IMAGE="portfolio-web:$old_release_ref"
        "${old_compose[@]}" up -d --no-build >/dev/null
        verify_health "$old_current" || fail 'rollback health checks failed'
        ln -sfn "$old_current" "$remote_root/current.next"
        mv -Tf "$remote_root/current.next" "$remote_root/current"
    else
        "${compose[@]}" down --remove-orphans >/dev/null 2>&1 || true
        rm -f "$remote_root/current"
    fi
}

verify_health() {
    release_directory=$1
    public_url=$(awk -F= '/^PORTFOLIO_PUBLIC_URL=/{print substr($0, index($0, "=") + 1)}' "$config")
    public_url=${public_url%/}
    for path in /up /pt-BR/ /en/; do
        curl --fail --silent --show-error --location --max-time 20 "$public_url$path" >/dev/null || return 1
    done
    return 0
}

if [[ -n "$old_current" && -f "$old_current/compose.yaml" ]]; then
    old_compose=(docker compose --project-name "$project" --env-file "$config" -f "$old_current/compose.yaml")
    "${old_compose[@]}" down --remove-orphans >/dev/null
fi

rollback_active=1
on_error() {
    status=$?
    trap - ERR
    if [[ "$rollback_active" -eq 1 ]]; then
        rollback || true
    fi
    exit "$status"
}
trap on_error ERR

backup_name="volume:portfolio_app-storage:/backups/database-$release_ref.sqlite"
"${compose[@]}" run --rm --no-deps app sh -lc 'db="${DB_DATABASE:-database/database.sqlite}"; case "$db" in /*) source="$db";; *) source="/var/www/html/$db";; esac; if [ -f "$source" ]; then php -r '\''$db = new PDO("sqlite:" . $argv[1]); $db->exec("VACUUM INTO " . $db->quote($argv[2]));'\'' "$source" "$1"; fi' sh "/var/www/html/storage/backups/database-$release_ref.sqlite"
"${compose[@]}" run --rm --no-deps app php artisan migrate --force
"${compose[@]}" up -d --no-build

verify_health "$release" || { rollback; fail 'health or smoke checks failed'; }

cat > "$release/release.txt" <<EOF
release=$release_ref
artifact_sha256=$expected_checksum
operator=${USER:-unknown}
database_backup=$backup_name
EOF
ln -sfn "$release" "$remote_root/current.next"
mv -Tf "$remote_root/current.next" "$remote_root/current"
rollback_active=0
printf 'remote deploy: release %s is healthy\n' "$release_ref"
REMOTE_SCRIPT
