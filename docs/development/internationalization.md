# Portfolio internationalization

## Supported locales and fallback policy

- Supported route locales are `pt-BR` and `en`, defined in `config/i18n.php`.
- The canonical default locale is `pt-BR`. `/` and legacy URLs without a locale receive a permanent redirect to the corresponding `/pt-BR/...` URL; the application does not redirect based on IP or `Accept-Language`.
- `InitializeLocale` resets every request to the default before routing. `SetLocale` accepts only the route allowlist and applies the URL locale for that request. Locale state is not stored in session or shared between requests.
- Laravel interface-key fallback is `pt-BR`. Both interface catalogs are required to have the same complete key structure; tests fail when a translation is absent.
- Editorial project and contribution content is loaded only from the requested locale's catalog. A missing project translation is omitted from that locale's listing and its detail URL returns 404. Article listings and detail routes use only published Markdown in that locale; an untranslated article returns 404 there. The language selector falls back to that locale's Writing index, and SEO alternates omit unavailable translations. Editorial content is never silently borrowed from the other language.

## Route and identifier conventions

Route segments stay stable in English (`projects`, `writing`, `open-source`, `contact`) to preserve technical URLs. Project and article slugs are locale-neutral stable identifiers. All page routes use `/{locale}/...`; `/sitemap.xml`, `/robots.txt`, `/resume`, and Laravel's `/up` endpoint remain global. The former unprefixed content routes issue 301 redirects to Portuguese canonical routes and retain query parameters.

## Translation and content files

- `lang/{locale}/ui.php` contains interface text, page headings, accessibility labels, SEO descriptions, dates, errors, and source labels.
- `lang/{locale}/portfolio.php` contains localized editorial fields for each stable project slug and open-source record key. `config/portfolio.php` keeps neutral identifiers, URLs, technologies, repository evidence, and technical state values (`merged`, `discussion_open`, and related states).
- Markdown uses `resources/content/articles/{locale}/{slug}.md`. A matching slug identifies translations; front matter provides locale-specific title/description/date and `status: published|draft`. Only published, valid articles are returned. Markdown is rendered with raw HTML stripped and unsafe links disabled.

### Adding or updating a translation

1. Add the same complete key set to both `lang/pt-BR/ui.php` and `lang/en/ui.php`; keep the nested key structure identical.
2. Keep project identity, technology lists, evidence URLs, stable slugs, and contribution state in the neutral catalog. Translate only editorial descriptions in both matching `portfolio.php` catalogs.
3. For an article, create/edit both locale folders with the same stable slug. Translate the metadata and complete body, retain a truthful publication state and date, and review technical terminology before publishing.
4. Keep Markdown HTML disabled and preserve the escaping of Blade output. Do not add `env()` calls to views or translated content to JavaScript.
5. Test both locale URLs, the language switch, metadata/hreflang and sitemap entries, as well as missing-translation behavior.

To add a language, add it to the supported allowlist (the route constraint is generated from this list), create complete UI and portfolio catalogs and localized article content, add a tested locale label, and define its SEO/date formatting policy. Do not enable the locale until the completeness tests pass. Rebuild Laravel's configuration cache after changing supported-locale configuration:

```sh
php artisan config:clear
php artisan config:cache
```

## SEO and publication

Each localized page has a locale-specific canonical URL, `html lang`, Open Graph/Twitter metadata, and JSON-LD `inLanguage`. `hreflang` alternates include only equivalent published pages; `x-default` points to the Portuguese canonical URL only when it exists. Sitemap entries cover localized static pages, translated projects, and published articles, with alternates. The global robots endpoint references that sitemap. Unavailable pages use localized 404 content marked `noindex`.

## Editorial requirement for future work

**Every future issue that changes interface, content, routes, or metadata must implement and test both PT-BR and EN in the same change.** New user-facing strings belong in the locale catalogs, and new project/article content requires reviewed equivalents before publication. Update the route/content matrices and completeness tests whenever page types or locales change.

## Audited page/component matrix

| Page or component | PT-BR | EN | Verification |
|---|---|---|---|
| Shared layout, navigation, selector, footer, accessibility labels | `pt-BR` | `en` | Locale-key parity test; layout rendered in both locales |
| Homepage, featured project cards, open-source preview, CTAs | `pt-BR` | `en` | Localized routes and translated content assertions |
| About/profile and toolkit | `pt-BR` | `en` | Locale-specific UI catalog; route tests |
| Projects index and all five case studies | `pt-BR` | `en` | Every neutral project slug resolves in both catalogs; route tests |
| Open-source list and six records | `pt-BR` | `en` | Every neutral record has matching editorial fields and stable evidence/state |
| Writing index and two article translations | `pt-BR` | `en` | Matching slugs, published status, valid dates and body tests |
| Article detail and Markdown output | `pt-BR` | `en` | Safe Markdown rendering and XSS/link tests |
| Contact, email, social profiles and résumé CTA | `pt-BR` | `en` | Localized labels; optional contact/resume behavior tests |
| 404 state | `pt-BR` | `en` | Locale-aware error rendering and `noindex` |
| SEO metadata, canonical, alternates and structured data | `pt-BR` | `en` | Feature assertions per localized URL |
| Sitemap | `pt-BR` and `en` | `pt-BR` and `en` | XML route/alternate checks; published content only |
| Robots and résumé download endpoints | Global endpoints | Global endpoints | Deliberately locale-neutral; sitemap/resume tests |
