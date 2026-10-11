# Architecture overview

Laravel 13 is a server-rendered portfolio. Named GET routes dispatch to `PortfolioController`; Blade renders pages, `config/portfolio.php` stores manually curated projects, and front-matter Markdown under `resources/content/articles/` provides published writing. Public requests are read-only. SQLite is the local default but no portfolio schema is currently needed.

Runtime: PHP 8.4+ (the framework supports 8.3, but the locked development test stack requires 8.4; development currently uses PHP 8.5), Composer, Node build artifacts. Docker and the minimum CI matrix use PHP 8.4. See [components](components.md), [data model](data-model.md), [content management](content-management.md), and [diagrams](diagrams.md).
