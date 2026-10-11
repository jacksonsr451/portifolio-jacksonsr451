# Validation report

**Run date:** 2026-10-11  
**Workspace:** Windows 11 / PHP 8.5.7 / Composer 2.10.1 / Node 24.15.0 / npm 11.12.1 / Docker 29.4.1  
**Application:** Laravel 13.35.0; Docker runtime uses PHP 8.4-FPM behind Nginx 1.28.

## Local checks

| Command / check | Result |
|---|---|
| `composer test` | Pass — 15 tests, 134 assertions |
| `XDEBUG_MODE=coverage php artisan test --coverage --min=80` | Pass — 97.7% total coverage in configured first-party scope (15 tests, 134 assertions) |
| `composer lint` | Pass — Pint |
| `composer analyse` | Pass — Larastan/PHPStan level 5, no errors |
| `composer validate --strict` | Pass |
| `composer audit --locked` | Pass — no reported advisories |
| `npm ci` | Pass — lockfile install; zero audit findings reported |
| `npm run build` | Pass — Vite production bundle generated |
| `npm audit --audit-level=high` | Pass — zero vulnerabilities reported |
| `docker compose config --quiet` | Pass |
| `docker compose build` | Pass — app and Nginx images built from digest-pinned bases |
| Docker runtime smoke test | Pass — Nginx config valid; home, project detail, article detail, and `/up` returned HTTP 200; PHP app health was healthy |
| GitHub workflow YAML parse | Pass — all four workflow YAML files parsed locally |
| `php artisan route:list --except-vendor` | Pass — 10 named public/application routes listed |

The Docker smoke test used local port 18080 because port 8080 was already allocated on this host. Test containers and their temporary named volumes were stopped and removed afterward.

## Scope limits

- GitHub-hosted workflow runs have not occurred: this workspace has no remote GitHub repository configured. YAML syntax and the equivalent local PHP/npm/Docker checks were verified.
- CodeQL covers the repository's JavaScript/TypeScript and Actions workflows. PHP is not a supported CodeQL language.
- GitHub Dependency Review, CodeQL alerting, secret scanning/push protection, branch protection, and environment approvals depend on repository visibility, plan, and administrator settings. Workflow definitions do not activate those settings by themselves.
- No Lighthouse score, production accessibility audit, or browser/device matrix has been measured. Targets are recorded in `docs/design/design-system.md`.
- No production deployment was attempted. The hosting target and adapter are intentionally unselected.
- Candidate project repository URLs/functionality and open-source contribution records remain marked for verification; no external professional contact or approved résumé PDF was supplied.

## Reproduction

See [getting started](../development/getting-started.md) and the root [README](../../README.md). CI uses PCOV with the same `--min=80` threshold; local coverage above used the installed Xdebug coverage mode.
