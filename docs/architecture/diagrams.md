# Diagrams

## Request flow

```mermaid
sequenceDiagram
  participant B as Browser
  participant R as Laravel route
  participant C as PortfolioController
  participant S as Catalog / Markdown files
  participant V as Blade view
  B->>R: GET page or slug
  R->>C: dispatch named route
  C->>S: read curated content
  S-->>C: validated data
  C->>V: render server-side
  V-->>B: HTML + Vite assets
```

## Deployment topology (prepared container)

```mermaid
flowchart LR
  Internet --> TLS[Selected host TLS termination]
  TLS --> Nginx[Container Nginx]
  Nginx --> PHP[PHP-FPM non-root]
  PHP --> Files[Read-only app + writable storage]
  PHP --> DB[(Optional persistent SQLite volume)]
```

The production host remains unselected; the diagram describes the provided container shape, not an active deployed system.
