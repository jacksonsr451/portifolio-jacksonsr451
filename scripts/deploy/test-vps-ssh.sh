#!/usr/bin/env bash

set -Eeuo pipefail

script_dir=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
adapter="$script_dir/vps-ssh.sh"
root=$(CDPATH= cd -- "$script_dir/../.." && pwd)
artifact=$(mktemp)
mock_directory=$(mktemp -d)
trap 'rm -f "$artifact"; rm -rf "$mock_directory"' EXIT
printf artifact > "$artifact"

assert_fails() {
    if "$@" >/dev/null 2>&1; then
        printf 'expected failure: %s\n' "$*" >&2
        exit 1
    fi
}

assert_fails "$adapter" "$artifact" invalid 'ssh://user@example.test/opt/portfolio'
assert_fails env DEPLOY_CREDENTIAL=x DEPLOY_KNOWN_HOSTS=x "$adapter" "$artifact" "$(printf 'a%.0s' {1..40})" 'https://user@example.test/opt/portfolio'
assert_fails env DEPLOY_CREDENTIAL=x "$adapter" "$artifact" "$(printf 'a%.0s' {1..40})" 'ssh://user@example.test/opt/portfolio'

cat > "$mock_directory/ssh" <<'MOCK_SSH'
#!/usr/bin/env bash
if [[ "$*" == *'bash -s --'* ]]; then
    cat >/dev/null
    exit 1
fi
exit 0
MOCK_SSH
cat > "$mock_directory/scp" <<'MOCK_SCP'
#!/usr/bin/env bash
exit 0
MOCK_SCP
chmod +x "$mock_directory/ssh" "$mock_directory/scp"
assert_fails env DEPLOY_CREDENTIAL='private-key-fixture' DEPLOY_KNOWN_HOSTS='host-key-fixture' SSH_BIN="$mock_directory/ssh" SCP_BIN="$mock_directory/scp" \
    "$adapter" "$artifact" "$(printf 'a%.0s' {1..40})" 'ssh://user@example.test/opt/portfolio'

grep -q 'DEPLOYMENT_ENABLED' "$root/.github/workflows/deploy.yml"
grep -q 'DEPLOY_KNOWN_HOSTS' "$root/.github/workflows/deploy.yml"
grep -q 'portfolio-app:' "$adapter"
grep -q '/pt-BR/' "$adapter"
grep -q '/en/' "$adapter"
grep -q 'flock' "$adapter"
grep -q 'VACUUM INTO' "$adapter"
grep -q 'app-database:' "$root/compose.yaml"
printf 'deploy script tests passed\n'
