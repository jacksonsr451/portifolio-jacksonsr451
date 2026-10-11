# Test matrix

| Area | Check | Command |
|---|---|---|
| Homepage/navigation targets | Feature tests | `composer test` |
| Project catalog/details/invalid slug | Feature + content validation | `composer test` |
| Articles/front matter behavior/invalid slug | Feature tests | `composer test` |
| SEO and sitemap | Feature tests | `composer test` |
| Health endpoint | Feature test | `composer test` |
| PHP style/types/coverage (>=80% first-party scope) | Pint/Larastan/Pest coverage | `composer lint`, `composer analyse`, `php artisan test --coverage --min=80` (CI uses PCOV) |
| CSS/JS production compilation | Vite | `npm run build` |
| Image rendering, keyboard/browser behavior | Manual audit / future browser suite | Not automated yet |
