# VPS production runbook

This repository contains a reviewed SSH + Docker Compose adapter, but no public production deployment is configured or claimed. Keep the protected production variable `DEPLOYMENT_ENABLED=false` until every provisioning and validation step below is complete.

## Provision the VPS

1. Use a supported Linux distribution with Docker Engine, the Docker Compose plugin, `openssh-server`, `flock`, `curl`, and `sha256sum`.
2. Create a dedicated non-root deploy user. Grant it only the Docker permission required by the host policy and ownership of `/srv/portfolio`; do not use an interactive administrator account.
3. Create `/srv/portfolio/{config,releases,incoming,backups,run}` and set ownership for the deploy user. Keep `/srv/portfolio/config/production.env` outside the repository and mode `0600`.
4. Put this configuration in `production.env` without committing it:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_KEY=replace-with-a-random-production-key
APP_URL=https://example.invalid
PORTFOLIO_PUBLIC_URL=https://example.invalid
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
SESSION_SECURE_COOKIE=true
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
LOG_CHANNEL=stderr
HTTP_PORT=8080
```

5. Provision encrypted off-host backups for `/srv/portfolio` data. The adapter creates a consistent SQLite backup before migrations in the persistent storage volume; it does not replace scheduled backups or restore testing.

## DNS and TLS

Point the production DNS record to the VPS, restrict the firewall to SSH and the TLS reverse-proxy ports, and terminate TLS with a maintained reverse proxy or managed certificate service. Proxy HTTPS traffic to the Compose `web` service on `127.0.0.1:${HTTP_PORT}`. Do not expose the application port publicly. Verify the final certificate, redirect, `APP_URL`, and `PORTFOLIO_PUBLIC_URL` before enabling the workflow.

## GitHub Environment

Create a protected environment named `production` and require review. Configure:

- `DEPLOYMENT_ENABLED=false` until public smoke tests and rollback are complete.
- `DEPLOY_TARGET=ssh://deploy@host:22/srv/portfolio`.
- `DEPLOY_ADAPTER=vps-ssh.sh`.
- Secret `DEPLOY_CREDENTIAL`: a dedicated SSH private key with no passphrase prompt and no reuse elsewhere.
- Secret `DEPLOY_KNOWN_HOSTS`: the exact pinned host key line(s) for the VPS. Never disable host-key verification.

The workflow accepts only a full commit SHA already reachable from `main`, builds both pinned images, and passes the artifact to the adapter. No secret is stored in the image or repository.

## Deployment behavior

The adapter uploads the image tarball, Compose file, and SHA-256 manifest into a release directory named by the commit SHA. It verifies the checksum, loads and inspects both images, acquires a remote `flock`, stops the current Compose project, creates a SQLite `VACUUM INTO` backup, runs migrations, starts the new images, and checks `/up`, `/pt-BR/`, and `/en/` through `PORTFOLIO_PUBLIC_URL`. The fixed Compose project name keeps `app-storage` and `app-database` volumes stable across releases.

The application image is immutable; configuration, database, sessions, cache, uploads, and logs remain external. The deploy user must not edit release files manually.

## Operational validation

After provisioning, run one manual deploy with a reviewed SHA. Confirm the public HTTPS pages, localized smoke routes, logs, database records, articles, translations, project data, résumé storage, and backup artifact. Only then change `DEPLOYMENT_ENABLED` to `true` through the protected environment and record the operator and release SHA.

No public production deployment is configured or claimed by this repository.
