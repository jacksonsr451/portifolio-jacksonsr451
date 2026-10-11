# Acceptance criteria

- Public routes return server-rendered pages; unknown slugs return 404.
- Portfolio claims are evidence-backed; unknown project/contribution facts are explicitly held for verification.
- Articles have required scalar metadata; only published articles are visible; Markdown is rendered through the framework renderer.
- Canonical, Open Graph, Person JSON-LD, sitemap, robots config, and health endpoint are available.
- Keyboard users can skip to content, navigate, focus, and toggle theme; reduced motion is respected.
- Required CI checks fail on real failures. Security workflow claims remain subject to repository feature/settings availability.
- Deployment stays disabled until target and explicit configuration exist.
- Local setup and production prerequisites are reproducible and documented.
