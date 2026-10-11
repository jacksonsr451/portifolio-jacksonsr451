# ADR 0003: Content storage

- **Status:** Accepted
- **Context:** A small portfolio does not need editing workflows, accounts, or relational content queries.
- **Decision:** Keep curated project data in PHP configuration and articles as Markdown with lightweight front matter.
- **Alternatives:** Database-backed CMS, external CMS, JSON catalog.
- **Consequences:** Reviewable diffs and deploy-time publication; no admin interface; front matter validation and repository review are important.
