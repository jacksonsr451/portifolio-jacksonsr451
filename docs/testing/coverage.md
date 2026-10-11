# Coverage

Engineering target: at least 80% line coverage for relevant first-party application code. CI installs PCOV and runs `php artisan test --coverage --min=80`. PHPUnit includes `app/` and excludes the unused Laravel starter `app/Models/User.php`; vendor code, Blade templates, route definitions, CSS, and JavaScript are outside the coverage denominator. The current local Xdebug run reports 97.8% for that scoped PHP source. The threshold does not measure UI/browser behavior or branch coverage and must not be presented as overall portfolio coverage.
