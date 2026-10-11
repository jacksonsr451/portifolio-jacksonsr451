# ADR 0002: Server-rendered pages

- **Status:** Accepted
- **Context:** Portfolio pages are primarily readable content and benefit from crawlability and low client complexity.
- **Decision:** Render Blade HTML on the server; JavaScript is limited to a progressive color-theme toggle.
- **Alternatives:** SPA, client rendering, Livewire for all views.
- **Consequences:** Fast content discovery, conventional routes, reduced client bundle; future interactive needs must justify added client/server state complexity.
