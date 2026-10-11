# Getting started

Requirements: PHP 8.4+, Composer 2, Node 22+, npm, SQLite/PDO SQLite. PHP 8.4 is the effective minimum because the selected PHPUnit 13 development toolchain requires it. Follow root README. Run `npm ci` and `npm run build` before Blade feature tests so `public/build/manifest.json` exists. Then run `composer test`, `php artisan test --coverage --min=80`, `composer lint`, and `composer analyse`. `php artisan serve` runs the app; `npm run dev` provides Vite hot reload in a second terminal.
