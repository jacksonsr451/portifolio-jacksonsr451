# ADR 0001: Laravel stack

- **Status:** Accepted
- **Context:** The site needs a maintained PHP foundation with server-rendered templates and a useful local testing ecosystem.
- **Decision:** Laravel 13, PHP 8.3 minimum (prefer 8.4+; currently tested locally on 8.5), Blade, Vite/Tailwind, SQLite local default, Pest 5/PHPUnit 13, Pint, and Larastan 3.
- **Alternatives:** Static-only generator; older Laravel release; SPA stack.
- **Consequences:** Mature conventions and tooling with Composer/npm lockfiles; PHP 8.3+ runtime requirement. No Livewire dependency is warranted for currently static pages.
