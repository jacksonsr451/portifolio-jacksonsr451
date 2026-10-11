<?php

use Illuminate\Support\Str;

it('renders the homepage with professional positioning and selected projects', function () {
    $this->get('/')->assertOk()->assertSee('Backend Engineer')->assertSee('EventFlow')->assertSee('Jackson Severino da Rocha');
});

it('renders each public section', function (string $path) {
    $this->get($path)->assertOk();
})->with(['/about', '/projects', '/open-source', '/writing', '/contact']);

it('shows a project detail with structured case-study sections', function () {
    $this->get('/projects/eventflow')->assertOk()->assertSee('Technology stack')->assertSee('Awaiting repository verification.')->assertSee('Problem statement')->assertSee('Architecture')->assertSee('Testing strategy');
});

it('returns not found for unknown or malformed project slugs', function () {
    $this->get('/projects/does-not-exist')->assertNotFound();
    $this->get('/projects/Bad_Slug')->assertNotFound();
});

it('lists published articles and renders markdown content safely', function () {
    $this->get('/writing')->assertOk()->assertSee('Designing idempotent API operations');
    $this->get('/writing/idempotent-apis')->assertOk()->assertSee('check-then-insert');
    $this->get('/writing/missing-article')->assertNotFound();
});

it('strips raw HTML and unsafe links from published Markdown', function () {
    $slug = 'markdown-security-'.Str::uuid();
    $path = resource_path("content/articles/{$slug}.md");
    $content = <<<MARKDOWN
    ---
    slug: {$slug}
    title: Markdown security regression check
    description: Test dangerous HTML and URL handling.
    date: 2026-10-11
    status: published
    ---

    Safe article text.

    <img src=x onerror=alert(1)>

    [unsafe](javascript:alert(1))
    MARKDOWN;

    file_put_contents($path, $content);

    try {
        $this->get("/writing/{$slug}")
            ->assertOk()
            ->assertSee('Safe article text')
            ->assertDontSee('<img', false)
            ->assertDontSee('onerror', false)
            ->assertDontSee('href="javascript:', false);
    } finally {
        if (is_file($path)) {
            unlink($path);
        }
    }
});

it('publishes canonical metadata and structured data', function () {
    $this->get('/')->assertOk()->assertSee('rel="canonical"', false)->assertSee('application/ld+json', false)->assertSee('og:title', false);
});

it('serves a sitemap containing public routes and content', function () {
    $response = $this->get('/sitemap.xml')->assertOk()->assertHeader('content-type', 'application/xml; charset=UTF-8')->assertSee('/projects/eventflow')->assertSee('/writing/idempotent-apis');

    expect(simplexml_load_string($response->getContent()))->toBeInstanceOf(SimpleXMLElement::class);
});

it('serves the framework health endpoint', function () {
    $this->get('/up')->assertOk();
});

it('serves robots rules with an absolute sitemap address', function () {
    $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: http://localhost/sitemap.xml');
});
