# Testing strategy

Pest tests exercise representative public routes, content details and 404s, Markdown listing/detail, metadata, sitemap, `/up`, and required project fields. Tests use local content and SQLite in-memory settings and make no external network calls. CI adds Pint, Larastan, a production Vite build, and an enforced 80% minimum line coverage for first-party `app/` code in scope.
