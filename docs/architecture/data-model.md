# Data model

The current read-only application has no portfolio database tables. Project records are PHP arrays validated by `tests/Unit/ContentValidationTest.php`: slug, title, summary, status, stack, and case-study narrative fields. Article records are Markdown files with required `slug`, `title`, `description`, `date`, and `status` front matter; only `published` articles enter public listings and sitemap. Laravel's default user/cache/job migrations are framework starter artifacts and are unused by portfolio features.

If content volume or editorial workflow later justifies persistence, define an explicit migration, constraints, publication state, and migration/rollback plan before introducing models.
