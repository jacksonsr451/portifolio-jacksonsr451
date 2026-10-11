# Pre-push technical audit

**Audit date:** 2026-10-11  
**Auditor scope:** repository source/configuration, CI/security/release/deploy workflows, Docker/Compose, local dependency/test/build checks, and publication readiness.  
**Decision:** **NO-GO for a new public push from this workspace today.** The code and local gates are ready after the fixes below, but the existing remote target and commit-email privacy need owner confirmation. This workspace already contains a commit and an `origin` remote; no commit, remote configuration, fetch, or push was performed by this audit.

## Executive summary

The baseline had three reproducible defects: GitHub Actions still referenced Node 20 actions after GitHub's Node 20 runner removal date; the sitemap Blade template failed under the PHP 8.4 container runtime; and raw HTML in versioned Markdown was passed through without sanitization. The deployment artifact also omitted the Nginx image, and the deploy input accepted mutable refs without re-running quality checks. These were corrected, with regression checks added where practical.

The application now passes local tests on PHP 8.5 and the full test suite on PHP 8.4, Pint, Larastan/PHPStan, Composer/npm audits, frontend build, workflow YAML parsing, Docker image build, and a rootless Compose smoke test. GitHub-hosted workflows and repository security/branch settings have not been run or configured by this audit.

## Findings by severity

### Critical

**None found.**

### High

| Finding | Evidence | Correction / status |
|---|---|---|
| GitHub Actions used Node 20 runtimes after the hosted-runner removal date. | The pinned checkout v4, setup-node v4, setup-php 2.36, and CodeQL 3.29 action manifests declare `runs.using: node20`. GitHub's published removal date was 2026-09-16; audit date is 2026-10-11. | Updated to full, verified Node 24 action commit SHAs: checkout 7.0.1, setup-node 7.1.0, setup-php 2.40.0, CodeQL 4.38.3. Dependency Review, cache, and Gitleaks actions are also pinned to Node 24-compatible commits. Workflow YAML parses locally; a hosted workflow run remains outstanding. |
| Sitemap returned HTTP 500 on PHP 8.4. | `docker run ... php:8.4-fpm-alpine php artisan test` failed on the sitemap assertion with `ParseError: unexpected identifier "version"` from the literal `<?xml version=...?>` in a compiled Blade view. Local PHP 8.5 did not expose this parser difference. | Removed the optional XML declaration and added a `SimpleXML` parse assertion. The full PHP 8.4 suite and Docker sitemap smoke test now pass. |
| Markdown articles allowed raw HTML event-handler markup to reach the browser. | `Str::markdown('<script>...')</script><img src=x onerror=...>')` preserved an HTML image tag with an event handler under default options; the article view intentionally emits rendered HTML unescaped. | Configured CommonMark to strip raw HTML and reject unsafe links. Added a route-level regression test for event-handler HTML and a `javascript:` link. |

### Medium

| Finding | Evidence | Correction / status |
|---|---|---|
| Declared PHP minimum did not match the test toolchain. | Composer declared `php: ^8.3`, while PHPUnit 13 requires PHP 8.4; Docker runtime was PHP 8.4 and local/CI previously centered on PHP 8.5. | Raised the project Composer minimum to PHP 8.4 and added CI coverage on PHP 8.4 and 8.5. README/ADR/getting-started docs now state the effective requirement. |
| Deployment accepted mutable refs and had no mandatory source quality gates. | The dispatcher accepted arbitrary branch/tag strings, then invoked a target adapter after checking only configuration. | It now accepts only a full 40-character commit SHA, verifies the commit is reachable from the default branch, and runs Pint, Larastan, Composer/npm audits, Pest with the 80% minimum, and Vite build before image creation. Deployment remains disabled without protected variables, credential, and an adapter. |
| Deployment artifact did not contain the complete Compose application. | The previous workflow saved only the PHP-FPM image, while the runtime topology also requires Nginx. | It now builds and bundles both `runtime`/PHP-FPM and `web`/Nginx images under the same full commit SHA for the adapter. |
| Release validation was weaker than CI. | Release previously did not enforce the coverage threshold or both dependency audits and accepted tags based only on a `v` prefix. | It now validates SemVer-like version tags and their commit on the default branch, runs migrations and the same quality/audit gates, and creates a draft release only. |
| Production Compose inherited DB-backed session/cache defaults without provisioning their tables, and session cookies had no `Secure` flag by default. | Before correction, the container reported `session.driver=database`; an HTTP response lacked `Secure`. Compose did not run migrations for its SQLite volume. | Compose now uses file sessions/cache and sync queue with the persisted app-storage volume, and removes the unused database volume. Production defaults `SESSION_SECURE_COOKIE=true`; local Compose can override it for HTTP stateful testing. Container config and response headers were rechecked. |
| Nginx master ran as root despite listening on an unprivileged port. | The official Nginx base image defaulted to UID 0. | Added an Nginx main configuration with `/tmp` PID/temp paths, switched to UID 101, read-only root filesystem, dropped all Linux capabilities, and enabled `no-new-privileges`. Runtime smoke tests pass. PHP-FPM runs as UID 82. |
| Unused default scaffold dependencies expanded the runtime/frontend surface. | `laravel/tinker`, `laravel/pail`, and Axios/bootstrap JS had no application call sites. | Removed Tinker, Pail, Axios, and the unused JS bootstrap file; refreshed both lockfiles. |
| Coverage figure is narrower than it may appear. | PHPUnit coverage includes PHP under `app/`, excludes the unused starter User model, and does not include Blade, route definitions, CSS, or JavaScript. It is line coverage, not branch coverage. | Documented the exact denominator. The reported 97.8% must be described as scoped application-PHP coverage, not total application/UI coverage. Further branch and browser/accessibility tests remain backlog items. |

### Low

| Finding | Status |
|---|---|
| The tracked PR template has a 225-line working-tree version versus the 14-line `HEAD` version; it was not part of the requested technical fixes. | Preserved the expanded checklist and only normalized trailing whitespace in its blank placeholders so `git diff --check` passes. The template remains unstaged; owner review is needed before committing it. |
| Repository screenshot placeholder remains. | Clearly marked pending in README; no screenshot is claimed. |
| Lighthouse/accessibility/browser results are not available. | Targets are documented, but no scores or conformance claims are made. |
| Only two technical articles are published. | Additional requested topics remain in the backlog; no breadth claim beyond the actual articles is made. |

## Evidence and commands executed

| Command/check | Result |
|---|---|
| `composer test` | Pass — 16 tests, 140 assertions on PHP 8.5 |
| `$env:XDEBUG_MODE='coverage'; php artisan test --coverage --min=80` | Pass — 97.8% line coverage in configured `app/` PHP scope |
| `docker run --rm -v "${PWD}:/var/www/html" -w /var/www/html php:8.4-fpm-alpine php artisan test` | Pass — 16 tests, 140 assertions on PHP 8.4 |
| `composer lint` | Pass — Pint |
| `composer analyse` | Pass — Larastan/PHPStan level 5, no errors |
| `composer validate --strict` | Pass |
| `composer audit --locked --no-interaction` | Pass — no advisories reported |
| `npm ci` | Pass — 60 packages installed from lockfile after Axios removal |
| `npm run build` | Pass — Vite production build |
| `npm audit --audit-level=high` | Pass — zero findings |
| `docker compose config --quiet` | Pass |
| `docker compose up -d --build` | Pass — PHP-FPM and Nginx image build |
| Compose runtime inspection | Pass — session/cache stores are file-backed; session cookie secure flag true; PHP UID 82, Nginx UID 101 |
| `docker compose exec -T web nginx -t` | Pass |
| Docker HTTP smoke checks | Pass — home, sitemap, and `/up` HTTP 200; earlier project/article endpoints also returned HTTP 200 |
| Python YAML parse of `.github/workflows/*.yml` | Pass — all four workflow files parse |
| `git diff --check` | Pass after PR-template whitespace cleanup |
| `git ls-files` checks for `.env`, SQLite database, and runtime log | None are tracked; local instances are ignored |
| Tracked-file credential-pattern scan | No matches for common app-key, private-key, GitHub-token, or AWS access-key formats |

Workflow action SHAs were checked against GitHub release/tag metadata and their pinned `action.yml` runtime declarations. The audited workflow actions now declare Node 24. GitHub-hosted workflow execution itself was not available from this local audit.

## Coverage critique

The 97.8% result is not a broad UI or end-to-end coverage figure. It covers PHP files under `app/` only. Existing positive/negative cases include route 404s, malformed project slugs, article not found, article content rendering, unsafe Markdown, and XML sitemap parsing. Remaining untested or shallow areas include:

- `publishedArticles()` behavior for draft/invalid-date/missing-field files and directory/file edge cases.
- Resume-present and contact-email-present branches.
- Sitemap behavior with malformed content and all SEO metadata per content route.
- Route definitions and Blade conditional branches as coverage sources.
- JavaScript theme persistence, keyboard/screen-reader/browser behavior, mobile layouts, contrast, and real Lighthouse/axe results.
- Production proxy/TLS/session behavior on an actual selected host.

Do not cite 97.8% without the `app/` PHP scope. CI enforces the 80% scoped line threshold; branch coverage is not collected.

## Repository and privacy state

- Existing HEAD is `6fa6451` (`feat: initialize portfolio website with Laravel and Tailwind CSS`). The current branch is `main`, it already tracks `origin/main`, and the local tracking refs report 0 ahead / 0 behind. This is not a pre-first-commit repository state.
- `origin` was already configured as `https://github.com/jacksonsr451/portifolio-jacksonsr451.git`. An unauthenticated GitHub URL lookup returned 404; this may mean the repository is private or unavailable. No fetch or remote change was made.
- The public GitHub profile `jacksonsr451` resolves and matches the supplied owner name/positioning; `.github/CODEOWNERS` references that account.
- The current commit author email is not a GitHub `noreply` address. Its exact value is deliberately omitted; the owner should decide whether it is suitable for public commit metadata.
- A local `.env` with a generated `APP_KEY`, SQLite file, and Laravel log exist but are ignored and not tracked/staged. Do not add them.
- Audit changes are in the worktree and unstaged. No commit, remote configuration, fetch, or push was performed.

## Pending external configuration and content

1. Confirm the origin repository exists, access/visibility, intended default branch, and whether the existing commit is already published.
2. Review whether the existing commit author email may be public; use the GitHub noreply address for future commits if preferred.
3. Configure branch protection with required `quality (8.4)` and `quality (8.5)` checks, reviewer policy, and merge strategy. Enable CodeQL/secret-scanning/push-protection settings available for the repository plan.
4. Run the workflows on the actual GitHub repository and review permissions, action runtime compatibility, Dependabot, CodeQL, dependency-review, and Gitleaks results.
5. Keep production deployment disabled until a hosting target, reviewed deploy adapter, protected `production` environment/reviewers, variables `DEPLOYMENT_ENABLED`, `DEPLOY_TARGET`, `DEPLOY_ADAPTER`, and secret `DEPLOY_CREDENTIAL` exist.
6. Verify project repository URLs, scope, technology stacks, project states, and open-source PR/merge states. No contribution acceptance is claimed without merged evidence.
7. Obtain owner-approved email, LinkedIn URL, and résumé PDF. The GitHub profile is verified; other links remain unpublished.
8. Add an approved screenshot and perform browser/device accessibility, performance, and SEO measurement. Do not claim Lighthouse targets until measured.

## Decision

**NO-GO for a new public push from this workspace until items 1–2 above are confirmed.** The application itself passes local quality gates on PHP 8.4/8.5 and the Docker runtime, and the remediation work is ready for review. This audit did not push or create a commit. The configured remote and existing commit mean the requested “first commit/push” premise must be clarified before publication.
