# Environments

Local: SQLite, `.env`, `php artisan serve`, optional Vite dev server. CI: fresh checkout, in-memory SQLite, generated ephemeral application key, no production secrets. Production: not selected or configured. A production environment must define `APP_ENV=production`, `APP_DEBUG=false`, canonical `APP_URL`, HTTPS, secure key, persistent writable storage, health probe `/up`, and an explicitly chosen database/storage approach.
