# Contributing

Use focused branches and pull requests. Explain the behavior change, risks, and how it was verified. Content changes must be supported by sources and must not add unverified employment, project outcomes, contribution acceptance, or contact details.

Before opening a PR, run `npm ci` and `npm run build` first: Blade feature tests use Vite's generated manifest. Then run `composer test`, `php artisan test --coverage --min=80` (with a coverage driver), `composer lint`, and `composer analyse`. Keep changes consistent with Laravel conventions and accessible semantic HTML. Do not add new dependencies without explaining their functional need.

Reviews should check correctness, security, accessible behavior, content evidence, and migration/deployment impact. Prefer squash merge after required checks pass. Repository administrators should protect the default branch and require the CI status check and at least one review when collaborators are available.
