# ADR 0005: Deployment strategy

- **Status:** Accepted
- **Context:** No production provider, address, or credential has been selected.
- **Decision:** Prepare Docker and an explicitly gated GitHub workflow; do not deploy until a reviewed provider adapter and protected environment secrets exist.
- **Alternatives:** Assume a hosting vendor; SSH to invented infrastructure; disable all deployment preparation.
- **Consequences:** No accidental deployment; owner must supply target-specific artifact upload, migration, health-check, and rollback implementation before enablement.
