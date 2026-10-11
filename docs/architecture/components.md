# Components

- `routes/web.php`: named public pages, constrained content slugs.
- `app/Http/Controllers/PortfolioController.php`: page assembly, Markdown discovery/rendering, sitemap.
- `config/portfolio.php`: curated projects and contribution state. Links remain null until verified.
- `resources/content/articles/*.md`: validated published article metadata and Markdown body.
- `resources/views`: accessible Blade templates and shared shell.
- `resources/css/app.css`, `resources/js/app.js`: Tailwind Vite entry, responsive styling, persisted theme preference.
- Laravel `/up`: framework health check.

There is no CMS, admin surface, contact submission, queue, or external API integration.
