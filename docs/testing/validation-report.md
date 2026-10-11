# Validation report

**Run date:** 2026-10-11  
**Workspace:** Windows 11 / PHP 8.5.7 / Composer 2.10.1 / Node 24.15.0 / npm 11.12.1 / Docker 29.4.1  
**Application:** Laravel 13.35.0; Docker runtime uses PHP 8.4-FPM behind Nginx 1.28.

## Local checks

| Command / check | Result |
|---|---|
| `composer test` | Pass — 16 tests, 140 assertions |
| `$env:XDEBUG_MODE='coverage'; php artisan test --coverage --min=80` | Pass — 97.8% coverage in the configured `app/` PHP scope (16 tests, 140 assertions) |
| `docker run --rm -v "${PWD}:/var/www/html" -w /var/www/html php:8.4-fpm-alpine php artisan test` | Pass — 16 tests, 140 assertions on the Docker production PHP version |
| `composer lint` | Pass — Pint |
| `composer analyse` | Pass — Larastan/PHPStan level 5, no errors |
| `composer validate --strict` | Pass |
| `composer audit --locked` | Pass — no reported advisories |
| `npm ci` | Pass — lockfile install; zero audit findings reported |
| `npm run build` | Pass — Vite production bundle generated |
| `npm audit --audit-level=high` | Pass — zero vulnerabilities reported |
| `docker compose config --quiet` | Pass |
| `docker compose build` | Pass — app and Nginx images built from digest-pinned bases |
| `$env:HTTP_PORT='18080'; docker compose up -d --build` plus `nginx -t`, `id`, and HTTP smoke checks | Pass — Nginx runs as UID 101 with read-only root filesystem; home, project detail, article detail, sitemap, and `/up` returned HTTP 200; PHP app health was healthy |
| Compose runtime config/header inspection | Pass — PHP-FPM UID 82, file-backed session/cache, production secure-cookie default true, and `Secure` observed on the session cookie |
| GitHub workflow YAML parse | Pass — all four workflow YAML files parsed locally; GitHub-hosted execution was not available |
| `php artisan route:list --except-vendor` | Pass — 10 named public/application routes listed |

The Docker smoke test used local port 18080 because port 8080 was already allocated on this host. Test containers and their temporary named volumes were stopped and removed afterward.

## Scope limits

- GitHub-hosted workflow runs have not occurred during this audit. An `origin` remote and `origin/main` tracking ref already exist in the workspace; the unauthenticated public repository URL returned 404, so repository visibility/access and remote freshness remain unverified. No remote configuration was changed.
- The application’s PHP 8.4 container test initially exposed a Blade/XML declaration parse error on `/sitemap.xml`; the XML declaration was removed and the endpoint now parses as XML in tests and returns HTTP 200 in the container smoke test.
- The first Markdown security probe found raw HTML event-handler markup survived the default renderer. Rendering now strips raw HTML and unsafe links, with an HTTP regression test for an event handler and `javascript:` URL.
- GitHub Actions references were upgraded to reviewed Node 24-compatible commit SHAs; the earlier checkout/setup-node/setup-php/CodeQL pins declared Node 20 runtimes, which are retired on current GitHub-hosted runners.
- Removed unused runtime/development dependencies `laravel/tinker` and `laravel/pail`, and removed `axios` plus the unused bootstrap JS entry.
- PHPUnit reports line coverage only for configured first-party `app/` PHP sources. Blade, route, CSS, and JS coverage is outside the 97.8% denominator; no branch-coverage percentage is claimed.
- Current commit history contains a non-GitHub-noreply author email. The address is not reproduced here; confirm it is intended for public commit metadata before making the repository public.
- CodeQL covers the repository's JavaScript/TypeScript and Actions workflows. PHP is not a supported CodeQL language.
- GitHub Dependency Review, CodeQL alerting, secret scanning/push protection, branch protection, and environment approvals depend on repository visibility, plan, and administrator settings. Workflow definitions do not activate those settings by themselves.
- No Lighthouse score, production accessibility audit, or browser/device matrix has been measured. Targets are recorded in `docs/design/design-system.md`.
- No production deployment was attempted. The hosting target and adapter are intentionally unselected.
- Candidate project repository URLs/functionality and open-source contribution records remain marked for verification; no external professional contact or approved résumé PDF was supplied.

## Reproduction

See [getting started](../development/getting-started.md) and the root [README](../../README.md). CI uses PCOV with the same `--min=80` threshold; local coverage above used the installed Xdebug coverage mode.
