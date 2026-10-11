# Threat model

## Assets and trust boundaries

Public content integrity, owner identity/contact details, deployment secrets, and runtime availability are the key assets. Browser requests cross into Laravel; content files are trusted reviewed repository inputs; CI receives repository code and possibly protected environment configuration.

## Threats and controls

- Malicious slug/path input: route regex constraints, lookup against curated lists, 404 on unknown identifiers.
- Stored script injection: article Markdown uses Laravel CommonMark; Blade escapes metadata and content fields.
- Credential disclosure: `.env` excluded, no credentials in content, deployment settings protected and gated.
- Dependency compromise: lockfiles, Composer/npm audit, Dependabot, least workflow permissions; human review remains required.
- DoS/spam: no contact form, comments, or user-submitted endpoints currently exist; host-level rate and resource limits remain target-specific.
- Supply-chain workflow risk: pin Actions to reviewed full commit SHAs and inspect Dependabot changes.

Residual: deployment host/TLS, persistence, edge protections, logging, and recovery are unconfigured pending provider selection.
