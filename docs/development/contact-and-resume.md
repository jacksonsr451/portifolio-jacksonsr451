# Professional contact and résumé

Contact values are read through `config/portfolio.php`; Blade templates never call `env()`. Empty, malformed, or unapproved values are omitted rather than rendered as links.

## Contact channels

- `PORTFOLIO_CONTACT_EMAIL` is optional. Set it only to an owner-approved professional email. The application validates the address before rendering the matching `mailto:` link.
- `PORTFOLIO_LINKEDIN_URL` and `PORTFOLIO_GITHUB_URL` default to the owner-provided LinkedIn profile and verified public GitHub profile. Only HTTPS URLs with the exact expected host and profile path are shown. Open-in-new-tab links use `rel="noopener noreferrer"`.
- Clear an optional URL value to disable it. Do not replace these values with guessed addresses, personal details, or unrelated profile URLs.

## Approved résumé PDF

The résumé is off by default. To publish one, first obtain explicit owner approval after reviewing the PDF content, links, and personal data. Put only that approved PDF in `storage/app/private/resumes/`, then configure:

```dotenv
PORTFOLIO_RESUME_PATH=resumes/approved-resume.pdf
PORTFOLIO_RESUME_APPROVED=true
```

`PORTFOLIO_RESUME_PATH` is a relative path inside the private local storage disk, under its `resumes/` directory. The application rejects traversal, absolute paths, external URLs, missing/unreadable files, and files without the `%PDF-` signature. It serves an approved file through the `/resume` download route as `application/pdf` with attachment disposition and `X-Content-Type-Options: nosniff`; it does not publish the storage directory as a static URL.

To disable the résumé, clear `PORTFOLIO_RESUME_PATH` and set `PORTFOLIO_RESUME_APPROVED=false` (or remove the file). The home and contact pages show no download CTA unless the approval flag and file checks pass. Never commit the PDF or private contact data.

## Apply configuration changes

Set values in the deployment environment's protected configuration store; do not add real contact details to `.env.example` or commit `.env`. After changing deployment variables, rebuild Laravel's config cache:

```sh
php artisan config:clear
php artisan config:cache
```

For local development, copy `.env.example` to `.env` and leave email/résumé unset unless using owner-approved data. The LinkedIn URL was supplied by the owner for this portfolio; automated access to LinkedIn returned HTTP 999 during review, so its live profile identity could not be independently fetched. The URL is nevertheless constrained to the exact supplied HTTPS profile address.
