# Local environment

Copy `.env.example` to `.env`, generate a unique local key using `php artisan key:generate`, and use SQLite. Keep `.env` local. `APP_URL` controls canonical and sitemap URLs. `PORTFOLIO_CONTACT_EMAIL` is optional and intentionally unset until an owner-approved address is supplied. The résumé is disabled unless an approved PDF is stored under private storage and both its relative path and approval flag are configured. See [professional contact and résumé setup](contact-and-resume.md); never point the résumé setting to a nonexistent or unapproved document.
