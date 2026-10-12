<?php

namespace App\Support;

use Illuminate\Http\Request;

class LocalizedRoutes
{
    public function __construct(
        private readonly LocalizedPortfolio $portfolio,
        private readonly LocalizedArticleRepository $articles,
        private readonly SupportedLocales $locales,
    ) {}

    /** @return array<string, string> */
    public function switchUrls(Request $request): array
    {
        $urls = [];
        foreach ($this->locales->codes() as $locale) {
            $urls[$locale] = $this->urlForLocale($request, $locale, true);
        }

        return $urls;
    }

    /** @return array<string, string> */
    public function alternates(Request $request): array
    {
        $route = $request->route();
        if ($route === null || ! str_starts_with((string) $route->getName(), 'localized.')) {
            return [];
        }

        $urls = [];
        foreach ($this->locales->codes() as $locale) {
            if ($this->hasEquivalent($route->getName(), $route->parameters(), $locale)) {
                $urls[$locale] = $this->urlForLocale($request, $locale, false);
            }
        }

        if (isset($urls[$this->locales->default()])) {
            $urls['x-default'] = $urls[$this->locales->default()];
        }

        return $urls;
    }

    private function urlForLocale(Request $request, string $locale, bool $preserveQuery): string
    {
        $route = $request->route();
        $name = $route?->getName();
        $parameters = $route?->parameters() ?? [];

        if (! is_string($name) || ! str_starts_with($name, 'localized.')) {
            return route('localized.home', ['locale' => $locale]);
        }

        if (! $this->hasEquivalent($name, $parameters, $locale)) {
            $name = $name === 'localized.projects.show'
                ? 'localized.projects.index'
                : 'localized.articles.index';
            $parameters = [];
        }

        unset($parameters['locale']);
        $parameters['locale'] = $locale;
        $url = route($name, $parameters);

        if ($preserveQuery && $request->query() !== []) {
            $url .= '?'.http_build_query($request->query());
        }

        return $url;
    }

    /** @param array<string, mixed> $parameters */
    private function hasEquivalent(string $name, array $parameters, string $locale): bool
    {
        if ($name === 'localized.projects.show') {
            return $this->portfolio->project($locale, (string) ($parameters['slug'] ?? '')) !== null;
        }

        if ($name === 'localized.articles.show') {
            return $this->articles->find($locale, (string) ($parameters['slug'] ?? '')) !== null;
        }

        return in_array($name, [
            'localized.home', 'localized.about', 'localized.projects.index', 'localized.open-source',
            'localized.articles.index', 'localized.contact',
        ], true);
    }
}
