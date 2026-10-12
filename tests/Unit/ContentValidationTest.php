<?php

use App\Support\LocalizedArticleRepository;
use App\Support\LocalizedPortfolio;
use App\Support\SupportedLocales;
use Illuminate\Support\Facades\File;

it('has unique valid project slugs and required case-study fields', function () {
    $projects = config('portfolio.projects');
    $slugs = array_column($projects, 'slug');

    expect($slugs)->toHaveCount(count(array_unique($slugs)));
    foreach ($projects as $project) {
        expect($project)->toHaveKeys(['slug', 'status_key', 'stack', 'featured', 'sources', 'repository', 'demo']);
        expect($project['slug'])->toMatch('/^[a-z0-9-]+$/');
        expect($project['sources'])->not->toBeEmpty();

        foreach ([...$project['sources'], ...array_filter([
            isset($project['repository']) ? ['url' => $project['repository']] : null,
            isset($project['demo']) ? ['url' => $project['demo']] : null,
        ])] as $reference) {
            expect(parse_url($reference['url'], PHP_URL_SCHEME))->toBe('https');
            expect(parse_url($reference['url'], PHP_URL_HOST))->not->toBeEmpty();
        }
    }

    foreach (app(SupportedLocales::class)->codes() as $locale) {
        $localized = app(LocalizedPortfolio::class)->projects($locale);
        expect(array_column($localized, 'slug'))->toEqual($slugs);
        foreach ($localized as $project) {
            expect($project)->toHaveKeys(['title', 'summary', 'status', 'problem', 'context', 'role', 'architecture', 'decisions', 'tradeoffs', 'testing', 'challenges', 'evidence']);

            foreach (['title', 'summary', 'status', 'problem', 'context', 'role', 'architecture', 'decisions', 'tradeoffs', 'testing', 'challenges', 'evidence'] as $field) {
                expect($project[$field])->toBeString()->not->toBeEmpty();
            }
        }
    }
});

it('validates published Markdown articles and matching translation identifiers', function () {
    $repository = app(LocalizedArticleRepository::class);
    $articlesByLocale = [];

    foreach (app(SupportedLocales::class)->codes() as $locale) {
        $articles = $repository->published($locale);
        expect($articles)->not->toBeEmpty();
        $slugs = array_column($articles, 'slug');
        expect($slugs)->toHaveCount(count(array_unique($slugs)));

        foreach ($articles as $article) {
            expect($article)->toHaveKeys(['slug', 'title', 'description', 'date', 'status', 'body']);
            expect($article['slug'])->toMatch('/^[a-z0-9]+(?:-[a-z0-9]+)*$/');
            expect($article['status'])->toBe('published');
            expect($article['date'])->toMatch('/^\d{4}-\d{2}-\d{2}$/');
            expect(DateTimeImmutable::createFromFormat('!Y-m-d', $article['date']))->toBeInstanceOf(DateTimeImmutable::class);
        }

        $articlesByLocale[$locale] = $articles;
    }

    expect(array_column($articlesByLocale['pt-BR'], 'slug'))->toEqual(array_column($articlesByLocale['en'], 'slug'));
    expect(array_column($articlesByLocale['pt-BR'], 'date', 'slug'))->toEqual(array_column($articlesByLocale['en'], 'date', 'slug'));
});

it('provides matching non-empty interface translation keys for each supported locale', function () {
    $english = require lang_path('en/ui.php');
    $portuguese = require lang_path('pt-BR/ui.php');
    $flatten = function (array $values, string $prefix = '') use (&$flatten): array {
        $keys = [];
        foreach ($values as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;
            if (is_array($value)) {
                $keys += $flatten($value, $path);
            } else {
                expect($value)->toBeString()->not->toBeEmpty();
                $keys[$path] = true;
            }
        }

        return $keys;
    };

    expect($flatten($portuguese))->toEqual($flatten($english));
    expect(config('i18n.default'))->toBe('pt-BR')
        ->and(app(SupportedLocales::class)->codes())->toBe(['pt-BR', 'en'])
        ->and(app(SupportedLocales::class)->supports('en-US'))->toBeFalse()
        ->and(config('i18n.locales.en.name'))->toBe('English')
        ->and(config('i18n.locales.en.enabled'))->toBeTrue()
        ->and(config('app.fallback_locale'))->toBe('pt-BR');

    $registry = config('i18n.locales');
    $registry['en-US'] = ['name' => 'English (United States)', 'enabled' => true, 'og_locale' => 'en_US'];
    $registry['zh-Hant'] = ['name' => '中文 (繁體)', 'enabled' => true, 'og_locale' => 'zh_TW'];
    $registry['invalid_locale'] = ['name' => 'Invalid', 'enabled' => true, 'og_locale' => 'invalid'];
    $registry['malformed-record'] = 'not a locale metadata record';
    $registry['missing-name'] = ['enabled' => true, 'og_locale' => 'xx_XX'];
    $registry['missing-og-locale'] = ['name' => 'Missing metadata', 'enabled' => true];
    config(['i18n.locales' => $registry]);

    expect(app(SupportedLocales::class)->codes())->toBe(['pt-BR', 'en'])
        ->and(app(SupportedLocales::class)->supports('en-US'))->toBeFalse()
        ->and(app(SupportedLocales::class)->supports('zh-Hant'))->toBeFalse()
        ->and(app(SupportedLocales::class)->supports('invalid_locale'))->toBeFalse()
        ->and(app(SupportedLocales::class)->supports('malformed-record'))->toBeFalse()
        ->and(app(SupportedLocales::class)->supports('missing-name'))->toBeFalse()
        ->and(app(SupportedLocales::class)->supports('missing-og-locale'))->toBeFalse();
});

it('localizes all open-source participation records without changing their technical state', function () {
    foreach (app(SupportedLocales::class)->codes() as $locale) {
        $items = app(LocalizedPortfolio::class)->contributions($locale);
        expect($items)->toHaveCount(count(config('portfolio.open_source')));

        foreach ($items as $item) {
            expect($item)->toHaveKeys(['scope', 'participation', 'status']);
            expect($item['state'])->toBeIn(['merged', 'discussion_completed_elsewhere', 'discussion_open']);

            foreach (['scope', 'participation', 'status'] as $field) {
                expect($item[$field])->toBeString()->not->toBeEmpty();
            }
        }
    }
});

it('omits portfolio items when their locale-specific editorial translation is missing', function () {
    $projects = config('portfolio.projects');
    $projects[0]['sources'][] = ['label' => 'missing-source-translation', 'url' => 'https://example.org/source'];
    $projects[] = [
        'slug' => 'untranslated-test-record',
        'status_key' => 'untranslated',
        'stack' => [],
        'featured' => false,
        'sources' => [],
        'repository' => null,
        'demo' => null,
    ];
    config(['portfolio.projects' => $projects]);

    $localized = app(LocalizedPortfolio::class)->projects('pt-BR');

    expect(array_column($localized, 'slug'))->not->toContain('eventflow')
        ->not->toContain('untranslated-test-record');

    $items = config('portfolio.open_source');
    $items[] = [...$items[0], 'key' => 'untranslated-test-record'];
    config(['portfolio.open_source' => $items]);

    expect(array_column(app(LocalizedPortfolio::class)->contributions('pt-BR'), 'key'))
        ->not->toContain('untranslated-test-record');
});

it('handles unsupported article locales and invalid or missing article identifiers safely', function () {
    $repository = app(LocalizedArticleRepository::class);
    expect($repository->published('../en'))->toBe([]);
    expect($repository->find('en', '../private'))->toBeNull();
    expect($repository->find('en', 'article-that-does-not-exist'))->toBeNull();
});

it('returns no published articles when a supported locale directory is absent', function () {
    $repository = app(LocalizedArticleRepository::class);
    File::shouldReceive('isDirectory')->once()->andReturnFalse();
    expect($repository->published('en'))->toBe([]);
});

it('ignores malformed Markdown without publishing it', function () {
    $repository = app(LocalizedArticleRepository::class);
    $path = resource_path('content/articles/en/malformed-fixture.md');
    file_put_contents($path, 'This file has no front matter.');

    try {
        expect($repository->find('en', 'malformed-fixture'))->toBeNull();
    } finally {
        if (is_file($path)) {
            unlink($path);
        }
    }
});

it('has neutral open-source records with valid evidence and unchanged technical states', function () {
    $contributions = config('portfolio.open_source');

    expect($contributions)->not->toBeEmpty();

    foreach ($contributions as $contribution) {
        expect($contribution)->toHaveKeys([
            'key', 'organization', 'repository', 'issue', 'pull_request', 'state', 'issue_url', 'pull_request_url',
        ]);
        expect($contribution['organization'])->not->toBeEmpty();
        expect($contribution['repository'])->not->toBeEmpty();
        expect($contribution['state'])->toBeIn(['merged', 'discussion_completed_elsewhere', 'discussion_open']);

        foreach (array_filter([$contribution['issue_url'], $contribution['pull_request_url'], $contribution['related_url'] ?? null]) as $url) {
            expect(filter_var($url, FILTER_VALIDATE_URL))->not->toBeFalse();
            expect(parse_url($url, PHP_URL_SCHEME))->toBe('https');
            expect(parse_url($url, PHP_URL_HOST))->toBe('github.com');
        }

        if ($contribution['state'] === 'merged') {
            expect($contribution['pull_request'])->toBeInt();
            expect($contribution['pull_request_url'])->not->toBeNull();
        } else {
            expect($contribution['pull_request'])->toBeNull();
            expect($contribution['pull_request_url'])->toBeNull();
        }
    }
});
