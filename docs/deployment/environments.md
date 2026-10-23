# Environments

Local: SQLite, `.env`, `php artisan serve` or `compose.yaml`, optional Vite dev server. CI: fresh checkout, in-memory SQLite, generated ephemeral application key, no production secrets. Production target: Linux VPS over SSH with Docker Compose, but not yet provisioned. Production must define `APP_ENV=production`, `APP_DEBUG=false`, canonical HTTPS `APP_URL` and `PORTFOLIO_PUBLIC_URL`, secure key, persistent `app-database` and `app-storage` volumes, health probe `/up`, TLS, encrypted backups, and an operator-reviewed recovery procedure.
