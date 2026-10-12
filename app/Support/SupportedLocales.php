<?php

namespace App\Support;

class SupportedLocales
{
    /** @return array<string, array{name:string,enabled:bool,og_locale:string}> */
    public function enabled(): array
    {
        return array_filter(
            config('i18n.locales', []),
            fn (mixed $locale, string $code): bool => is_array($locale)
                && ($locale['enabled'] ?? false) === true
                && $this->isLocaleTag($code)
                && is_string($locale['name'] ?? null)
                && $locale['name'] !== ''
                && is_string($locale['og_locale'] ?? null)
                && $locale['og_locale'] !== ''
                && is_file(lang_path($code.'/ui.php'))
                && is_file(lang_path($code.'/portfolio.php'))
                && is_dir(resource_path('content/articles/'.$code)),
            ARRAY_FILTER_USE_BOTH,
        );
    }

    /** @return list<string> */
    public function codes(): array
    {
        return array_keys($this->enabled());
    }

    public function supports(mixed $locale): bool
    {
        return is_string($locale) && array_key_exists($locale, $this->enabled());
    }

    public function default(): string
    {
        $default = (string) config('i18n.default');

        abort_unless($this->supports($default), 500);

        return $default;
    }

    private function isLocaleTag(string $locale): bool
    {
        return preg_match('/\A(?:[A-Za-z]{2,3}(?:-[A-Za-z]{3}){0,3}|[A-Za-z]{4}|[A-Za-z]{5,8})(?:-[A-Za-z]{4})?(?:-(?:[A-Za-z]{2}|[0-9]{3}))?(?:-(?:[A-Za-z0-9]{5,8}|[0-9][A-Za-z0-9]{3}))*(?:-[0-9A-WY-Za-wy-z](?:-[A-Za-z0-9]{2,8})+)*(?:-x(?:-[A-Za-z0-9]{1,8})+)?\z/', $locale) === 1
            || preg_match('/\Ax(?:-[A-Za-z0-9]{1,8})+\z/i', $locale) === 1;
    }
}
