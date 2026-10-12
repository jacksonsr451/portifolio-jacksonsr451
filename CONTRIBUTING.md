# Contributing

Use focused branches and pull requests. Explain the behavior change, risks, and how it was verified. Content changes must be supported by sources and must not add unverified employment, project outcomes, contribution acceptance, or contact details.

All user-facing interface, content, routes, and metadata changes must be implemented and tested in both Portuguese (Brazil) (`pt-BR`) and English (`en`) in the same PR. Add interface strings to both locale catalogs; add project and open-source editorial text to both localized portfolio catalogs; provide reviewed Markdown article translations under both locale directories before publishing. Do not add translated user-facing strings directly to Blade, controllers, or JavaScript. Follow [the internationalization guide](docs/development/internationalization.md) and keep its page/content matrix and parity tests current.

Before opening a PR, run `npm ci` and `npm run build` first: Blade feature tests use Vite's generated manifest. Then run `composer test`, `php artisan test --coverage --min=80` (with a coverage driver), `composer lint`, and `composer analyse`. Keep changes consistent with Laravel conventions and accessible semantic HTML. Do not add new dependencies without explaining their functional need.

Reviews should check correctness, security, accessible behavior, content evidence, and migration/deployment impact. Prefer squash merge after required checks pass. Repository administrators should protect the default branch and require the CI status check and at least one review when collaborators are available.
