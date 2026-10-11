# Testing strategy

Pest tests exercise representative public routes, content details and 404s, Markdown listing/detail, metadata, sitemap, `/up`, and required project fields. Tests use local content and SQLite in-memory settings and make no external network calls. CI adds Pint, Larastan, a production Vite build, and an enforced 80% minimum line coverage for first-party `app/` code in scope.

### Vite manifest and URL test configuration

Blade layouts use `@vite`, which requires `public/build/manifest.json`. CI, release, and deployment validation therefore run `npm ci` and `npm run build` before Blade feature tests; `public/build` remains generated and ignored by Git. PHPUnit forces `APP_URL=http://localhost` for test runs, and the robots assertion derives its expected sitemap URL with Laravel's `url()` helper. The developer `.env.example` may use `http://localhost:8000` for `artisan serve` without changing the deterministic test origin.
