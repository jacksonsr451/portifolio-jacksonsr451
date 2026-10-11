# Local environment

Copy `.env.example` to `.env`, generate a unique local key using `php artisan key:generate`, and use SQLite. Keep `.env` local. `APP_URL` controls canonical and sitemap URLs. `PORTFOLIO_CONTACT_EMAIL` and `PORTFOLIO_RESUME_PATH` are optional and intentionally unset; never point the resume setting to a nonexistent or unapproved document.
