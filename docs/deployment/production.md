# Production runbook (not yet target-specific)

1. Select and document a hosting provider and artifact deployment adapter.
2. Provision TLS, runtime/container support, persistent storage, backups, monitoring, and a `/up` probe. The supplied topology runs PHP-FPM as `www-data` and Nginx as UID 101, with read-only container root filesystems and explicit writable volumes/tmpfs.
3. Configure `APP_KEY`, `APP_URL`, `APP_ENV=production`, `APP_DEBUG=false`, and `SESSION_SECURE_COOKIE=true` behind HTTPS; keep secrets outside the image. The Laravel session-cookie default is secure when `APP_ENV=production`.
4. Build the PHP-FPM (`runtime`) and Nginx (`web`) images from the same validated commit; the deployment workflow bundles both under the full commit SHA. The target adapter must record image digests and source revision.
5. Check compatibility and backup before any schema migration. This application has no portfolio schema migrations beyond Laravel starter tables.
6. Deploy behind health checks; verify `/up`, a page, sitemap, and logs without exposing secret values.
7. Record release commit, artifact identifier, operator, and verification results.

No actual production deployment is configured or claimed.
