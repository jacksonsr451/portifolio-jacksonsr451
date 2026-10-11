# Security checklist

- [x] No contact form or arbitrary upload endpoints.
- [x] Constrained content slugs; escaped output; Markdown renderer used for article body.
- [x] `.env` ignored and placeholder-only `.env.example`.
- [x] Lockfiles and dependency audit workflows prepared.
- [ ] Configure GitHub secret scanning / push protection if available.
- [ ] Require review and CI through default-branch protection settings.
- [ ] Select host, configure TLS, production key and least-privileged deployment credentials.
- [ ] Validate production headers, backups, monitoring and incident contact after target selection.

Checked repository code only; workflow existence does not prove GitHub security settings are active.
