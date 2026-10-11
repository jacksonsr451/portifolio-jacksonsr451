<?php

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

it('publishes canonical metadata and structured data', function () {
    $this->get('/')->assertOk()->assertSee('rel="canonical"', false)->assertSee('application/ld+json', false)->assertSee('og:title', false);
});

it('serves a sitemap containing public routes and content', function () {
    $this->get('/sitemap.xml')->assertOk()->assertHeader('content-type', 'application/xml; charset=UTF-8')->assertSee('/projects/eventflow')->assertSee('/writing/idempotent-apis');
});

it('serves the framework health endpoint', function () {
    $this->get('/up')->assertOk();
});

it('serves robots rules with an absolute sitemap address', function () {
    $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: http://localhost/sitemap.xml');
});
