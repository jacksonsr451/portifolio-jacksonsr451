# ADR 0004: Testing strategy

- **Status:** Accepted
- **Context:** Route behavior, content exposure, SEO metadata, and build quality should be checked without live external services.
- **Decision:** Pest feature tests exercise public HTTP behavior; a unit test checks curated project structure; Pint, Larastan, and Vite build run in CI. Target 80% coverage for relevant application code when coverage measurement is configured.
- **Alternatives:** Manual-only checks; browser end-to-end suite for every change.
- **Consequences:** Deterministic, fast baseline checks. Coverage target is aspirational until a coverage driver and report gate are installed; it is not represented as currently measured.
