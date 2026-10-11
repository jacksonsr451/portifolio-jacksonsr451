# Production runbook (not yet target-specific)

1. Select and document a hosting provider and artifact deployment adapter.
2. Provision TLS, runtime/container support, persistent storage, backups, monitoring, and a `/up` probe.
3. Configure `APP_KEY`, `APP_URL`, `APP_ENV=production`, `APP_DEBUG=false`; keep secrets outside the image.
4. Build an immutable artifact from a validated commit/tag and record its digest/revision.
5. Check compatibility and backup before any schema migration. This application has no portfolio schema migrations beyond Laravel starter tables.
6. Deploy behind health checks; verify `/up`, a page, sitemap, and logs without exposing secret values.
7. Record release commit, artifact identifier, operator, and verification results.

No actual production deployment is configured or claimed.
