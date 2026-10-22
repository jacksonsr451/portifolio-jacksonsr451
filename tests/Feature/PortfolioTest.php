<?php

use App\Support\LocalizedArticleRepository;
use App\Support\SupportedLocales;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

it('renders the default-language homepage with professional positioning and selected projects', function () {
    $this->get('/pt-BR')->assertOk()->assertSee('Engenheiro de Backend')->assertSee('EventFlow')->assertSee('Jackson Severino da Rocha');
});

it('renders each public section', function (string $path) {
    $selectorLabel = str_starts_with($path, '/pt-BR/') ? 'Alterar idioma' : 'Change language';

    $this->get($path)
        ->assertOk()
        ->assertSee('aria-label="'.$selectorLabel.'"', false)
        ->assertSee('Português (Brasil)')
        ->assertSee('English');
})->with(['/pt-BR/about', '/pt-BR/projects', '/pt-BR/open-source', '/pt-BR/writing', '/pt-BR/contact', '/en/about', '/en/projects', '/en/open-source', '/en/writing', '/en/contact']);

it('localizes full page copy and open-source states in both locales', function () {
    $this->get('/pt-BR/about')->assertOk()->assertSee('Áreas em que atuo');
    $this->get('/en/about')->assertOk()->assertSee('What I work on');
    $this->get('/pt-BR/projects')->assertOk()->assertSee('Um catálogo selecionado.');
    $this->get('/en/projects')->assertOk()->assertSee('A curated catalog.');
    $this->get('/en/open-source')
        ->assertOk()
        ->assertSee('Merged')
        ->assertSee('Open issue — discussion only')
        ->assertSee('Issue completed via another PR; discussion only')
        ->assertSee('PR #779');
});

it('serves localized project details and article content for both locales', function (string $locale, string $expectedProject, string $expectedArticle) {
    $this->get("/{$locale}/projects/eventflow")->assertOk()->assertSee($expectedProject);
    $this->get("/{$locale}/projects/jackson-easy-api")->assertOk()->assertSee('jackson-easy-api');
    $this->get("/{$locale}/writing")->assertOk()->assertSee($expectedArticle);
    $this->get("/{$locale}/writing/idempotent-apis")->assertOk()->assertSee($expectedArticle);
})->with([
    'Português brasileiro' => ['pt-BR', 'Projeto educacional de arquitetura', 'idempotentes'],
    'English' => ['en', 'An educational distributed-systems design project', 'idempotent'],
]);

it('formats article dates in the active locale', function (string $locale, string $formattedDate) {
    $this->get("/{$locale}/writing/idempotent-apis")
        ->assertOk()
        ->assertSee($formattedDate);
})->with([
    'Português brasileiro' => ['pt-BR', '10 de outubro de 2026'],
    'English' => ['en', 'October 10, 2026'],
]);

it('renders every catalog project detail and published article in each supported locale', function () {
    $articles = app(LocalizedArticleRepository::class);

    foreach (app(SupportedLocales::class)->codes() as $locale) {
        foreach (config('portfolio.projects') as $project) {
            $this->get("/{$locale}/projects/{$project['slug']}")->assertOk();
        }

        foreach ($articles->published($locale) as $article) {
            $this->get("/{$locale}/writing/{$article['slug']}")->assertOk()->assertSee($article['title']);
        }
    }
});

it('redirects every legacy content URL to its Portuguese canonical route and preserves query parameters', function (string $legacyPath, string $routeName, array $parameters) {
    $expected = route($routeName, ['locale' => 'pt-BR', ...$parameters]);
    $this->get($legacyPath.'?campaign=old%20site')->assertStatus(301)->assertRedirect($expected.'?campaign=old+site');
})->with([
    'home' => ['/', 'localized.home', []],
    'about' => ['/about', 'localized.about', []],
    'projects' => ['/projects', 'localized.projects.index', []],
    'project detail' => ['/projects/eventflow', 'localized.projects.show', ['slug' => 'eventflow']],
    'open source' => ['/open-source', 'localized.open-source', []],
    'writing index' => ['/writing', 'localized.articles.index', []],
    'article' => ['/writing/idempotent-apis', 'localized.articles.show', ['slug' => 'idempotent-apis']],
    'contact' => ['/contact', 'localized.contact', []],
]);

it('uses Portuguese as the deterministic default regardless of browser language', function () {
    $this->withHeaders(['Accept-Language' => 'en'])
        ->get('/')
        ->assertStatus(301)
        ->assertRedirect(route('localized.home', ['locale' => 'pt-BR']));
});

it('resolves locale from each request and keeps translated content isolated', function () {
    $english = $this->get('/en')->assertOk()->assertSee('<html lang="en"', false)->assertSee('Building dependable');
    $portuguese = $this->get('/pt-BR')->assertOk()->assertSee('<html lang="pt-BR"', false)->assertSee('Construindo');

    expect(app()->getLocale())->toBe('pt-BR');
    $english->assertDontSee('Construindo');
    $portuguese->assertDontSee('Building dependable');
});

it('preserves the current route, slug, and query string in the language selector', function () {
    $this->get('/en/projects/eventflow?ref=language-menu')
        ->assertOk()
        ->assertSee('href="http://localhost/pt-BR/projects/eventflow?ref=language-menu"', false)
        ->assertSee('lang="en" hreflang="en"', false)
        ->assertSee('aria-current="page"', false);
});

it('supports a separately registered BCP 47 locale through the same routes and selector', function () {
    $langDirectory = lang_path('en-US');
    $articleDirectory = resource_path('content/articles/en-US');
    expect(File::exists($langDirectory))->toBeFalse();
    expect(File::exists($articleDirectory))->toBeFalse();

    File::makeDirectory($langDirectory, 0755, true);
    File::makeDirectory($articleDirectory, 0755, true);
    File::copy(lang_path('en/ui.php'), $langDirectory.'/ui.php');
    File::copy(lang_path('en/portfolio.php'), $langDirectory.'/portfolio.php');
    $portfolioFixture = require $langDirectory.'/portfolio.php';
    unset($portfolioFixture['projects']['erp-platform']);
    file_put_contents($langDirectory.'/portfolio.php', "<?php\n\nreturn ".var_export($portfolioFixture, true).";\n");

    foreach (File::files(resource_path('content/articles/en')) as $articleFile) {
        File::copy($articleFile->getPathname(), $articleDirectory.'/'.$articleFile->getFilename());
    }

    $locales = config('i18n.locales');
    $locales['en-US'] = ['name' => 'English (United States)', 'enabled' => true, 'og_locale' => 'en_US'];
    config(['i18n.locales' => $locales]);
    $this->artisan('portfolio:import-projects')->assertExitCode(0);

    try {
        $this->get('/en-US/projects/eventflow?ref=selector')
            ->assertOk()
            ->assertSee('<html lang="en-US"', false)
            ->assertSee('hreflang="en-US"', false)
            ->assertSee('property="og:locale" content="en_US"', false)
            ->assertSee('href="http://localhost/en/projects/eventflow?ref=selector"', false)
            ->assertSee('href="http://localhost/en-US/projects/eventflow?ref=selector"', false)
            ->assertSee('lang="en-US" hreflang="en-US"', false)
            ->assertSee('English (United States)');

        $this->get('/en-US/writing/idempotent-apis')->assertOk();
        $this->get('/en/projects/erp-platform')
            ->assertOk()
            ->assertSee('href="http://localhost/en-US/projects"', false)
            ->assertDontSee('rel="alternate" hreflang="en-US"', false);
        $this->get('/en-US/projects/erp-platform')
            ->assertNotFound()
            ->assertSee('href="http://localhost/en-US/projects"', false);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('/en-US/projects/eventflow')
            ->assertDontSee('http://localhost/en-US/projects/erp-platform', false);
    } finally {
        File::deleteDirectory($langDirectory);
        File::deleteDirectory($articleDirectory);
    }
});

it('rejects unsupported locales and malformed localized paths', function () {
    $this->get('/en')->assertOk();
    $this->get('/fr')->assertNotFound()->assertSee('Página não encontrada')->assertSee('noindex, follow');
    $this->get('/en/projects/not-a-project')->assertNotFound()->assertSee('Page not found');
    $this->get('/en/projects/../.env')->assertNotFound();
    $this->get('/en/writing/Bad_Slug')->assertNotFound();
});

it('rejects locales present in the registry but disabled for publication', function () {
    $locales = config('i18n.locales');
    $locales['es'] = ['name' => 'Español', 'enabled' => false, 'og_locale' => 'es_ES'];
    config(['i18n.locales' => $locales]);

    $this->get('/es/contact')->assertNotFound();
    $this->get('/pt-BR/contact')->assertOk()->assertDontSee('Español');
});

it('renders localized not-found, access-denied, expired, and server-error messages', function () {
    foreach (app(SupportedLocales::class)->codes() as $locale) {
        app()->setLocale($locale);

        foreach (['404', '403', '419', '500'] as $status) {
            $this->view("errors.{$status}")
                ->assertSee(__("ui.errors.{$status}_title"))
                ->assertSee(__("ui.errors.{$status}_message"))
                ->assertSee('noindex, follow');
        }
    }
});

it('uses the writing index as the safe language-switch fallback for an untranslated article', function () {
    $path = resource_path('content/articles/en/english-only-review.md');
    file_put_contents($path, "---\nslug: english-only-review\ntitle: English-only review\ndescription: A temporary translation availability fixture.\ndate: 2026-10-12\nstatus: published\n---\nPublished only in English.\n");

    try {
        $this->get('/en/writing/english-only-review')
            ->assertOk()
            ->assertSee('href="http://localhost/pt-BR/writing"', false)
            ->assertSee('hreflang="en"', false)
            ->assertDontSee('rel="alternate" hreflang="pt-BR"', false)
            ->assertDontSee('rel="alternate" hreflang="x-default"', false);

        $this->get('/pt-BR/writing/english-only-review')->assertNotFound();
    } finally {
        if (is_file($path)) {
            unlink($path);
        }
    }
});

it('generates localized SEO, alternate links, and global sitemap and robots endpoints', function () {
    $this->get('/pt-BR/projects/eventflow')
        ->assertOk()
        ->assertSee('<html lang="pt-BR"', false)
        ->assertSee('rel="canonical" href="http://localhost/pt-BR/projects/eventflow"', false)
        ->assertSee('hreflang="en" href="http://localhost/en/projects/eventflow"', false)
        ->assertSee('hreflang="x-default" href="http://localhost/pt-BR/projects/eventflow"', false)
        ->assertSee('property="og:locale" content="pt_BR"', false)
        ->assertSee('name="twitter:card"', false)
        ->assertSee('"inLanguage":"pt-BR"', false);

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee('/pt-BR/projects/eventflow')
        ->assertSee('/en/projects/eventflow')
        ->assertSee('/pt-BR/writing/api-error-contracts')
        ->assertSee('/en/writing/api-error-contracts')
        ->assertSee('hreflang="x-default"', false);

    $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: '.url('/sitemap.xml'));
});

it('keeps unpublished localized articles out of the sitemap', function () {
    $path = resource_path('content/articles/en/not-published.md');
    file_put_contents($path, "---\nslug: not-published\ntitle: Draft only\ndescription: A private draft.\ndate: 2026-10-12\nstatus: draft\n---\nDraft body.\n");

    try {
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('not-published');
        $this->get('/en/writing')->assertOk()->assertDontSee('Draft only');
        $this->get('/en/writing/not-published')->assertNotFound();
    } finally {
        if (is_file($path)) {
            unlink($path);
        }
    }
});

it('shows linked and correctly classified open-source contributions on the page and homepage', function () {
    $page = $this->get('/pt-BR/open-source')->assertOk();
    $page->assertSee('Eclipse Foundation / otterdog')
        ->assertSee('Issue #536')->assertSee('PR #765')
        ->assertSee('Integrada (merged)')->assertSee('Issue aberta — somente discussão')
        ->assertSee('https://github.com/eclipse-csi/otterdog/pull/779', false)
        ->assertSee('https://github.com/microsoft/PyRIT/issues/2748', false)
        ->assertSee('PR de conclusão #2989 (por outra pessoa contribuidora)')
        ->assertDontSee('Verification required');

    $this->get('/pt-BR')
        ->assertOk()
        ->assertSee('Contribuições com evidências.')
        ->assertSee('PR #765')
        ->assertSee('PR #779')
        ->assertDontSee('Verification required');
});

it('shows approved professional profile links and a valid configured email consistently', function () {
    config(['portfolio.email' => 'jackson@example.org']);

    $this->get('/pt-BR/contact')
        ->assertOk()
        ->assertSee('mailto:jackson@example.org', false)
        ->assertSee('Enviar e-mail para jackson@example.org')
        ->assertSee('https://www.linkedin.com/in/jackson-severino-da-rocha/', false)
        ->assertSee('https://github.com/jacksonsr451', false)
        ->assertSee('rel="noopener noreferrer"', false);

    $this->get('/en/contact')
        ->assertOk()
        ->assertSee('Email jackson@example.org')
        ->assertSee('Connect on LinkedIn')
        ->assertSee('View GitHub profile');

    $this->get('/pt-BR')
        ->assertOk()
        ->assertSee('LinkedIn')
        ->assertSee('GitHub')
        ->assertSee('href="'.route('localized.contact', ['locale' => 'pt-BR']).'"', false);
});

it('omits invalid or missing email and unsafe profile URLs', function () {
    config([
        'portfolio.email' => 'not-an-email',
        'portfolio.linkedin' => 'javascript:alert(1)',
        'portfolio.github' => 'https://attacker.example/jacksonsr451',
    ]);

    $this->get('/pt-BR/contact')
        ->assertOk()
        ->assertDontSee('mailto:', false)
        ->assertDontSee('Conecte-se pelo LinkedIn')
        ->assertDontSee('Ver perfil no GitHub')
        ->assertSee('Nenhum canal de contato profissional está configurado no momento.')
        ->assertDontSee('href="javascript:', false);
});

it('keeps approved profiles available when the professional email is unset', function () {
    config(['portfolio.email' => null]);

    $this->get('/pt-BR/contact')
        ->assertOk()
        ->assertSee('Conecte-se pelo LinkedIn')
        ->assertSee('Ver perfil no GitHub')
        ->assertDontSee('mailto:', false);
});

it('does not render a resume download while absent or unapproved', function () {
    config(['portfolio.resume' => '', 'portfolio.resume_approved' => false]);

    $this->get('/pt-BR')->assertOk()->assertDontSee('Baixar currículo');
    $this->get('/pt-BR/contact')->assertOk()->assertDontSee('Baixar currículo');
    $this->get('/resume')->assertNotFound();
});

it('serves an explicitly approved PDF from private storage as a download', function () {
    Storage::fake('local');
    Storage::disk('local')->put('resumes/approved-cv.pdf', "%PDF-1.7\nApproved test fixture\n%%EOF");
    config(['portfolio.resume' => 'resumes/approved-cv.pdf', 'portfolio.resume_approved' => true]);

    $this->get('/pt-BR')
        ->assertOk()
        ->assertSee('Baixar currículo (PDF)')
        ->assertSee('href="'.route('resume.download').'"', false);

    $this->get('/pt-BR/contact')->assertOk()->assertSee('Baixar currículo (PDF)');
    $this->get('/en')->assertOk()->assertSee('Download résumé (PDF)');
    $this->get('/en/contact')->assertOk()->assertSee('Download résumé (PDF)');

    $this->get('/resume')
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertHeader('x-content-type-options', 'nosniff')
        ->assertHeader('content-disposition', 'attachment; filename=approved-cv.pdf');
});

it('rejects missing, malformed, or unsafe resume paths', function (string $resumePath, bool $createFile, string $contents) {
    Storage::fake('local');

    if ($createFile && $resumePath !== '../.env') {
        Storage::disk('local')->put($resumePath, $contents);
    }

    config(['portfolio.resume' => $resumePath, 'portfolio.resume_approved' => true]);

    $this->get('/pt-BR')->assertOk()->assertDontSee('Baixar currículo');
    $this->get('/resume')->assertNotFound();
})->with([
    'missing PDF' => ['resumes/missing.pdf', false, ''],
    'non-PDF content' => ['resumes/not-a-pdf.pdf', true, 'plain text'],
    'path traversal' => ['../.env', false, ''],
    'external URL' => ['https://attacker.example/resume.pdf', false, ''],
]);

it('rejects a directory configured as the résumé PDF', function () {
    Storage::fake('local');
    Storage::disk('local')->makeDirectory('resumes/directory.pdf');
    config(['portfolio.resume' => 'resumes/directory.pdf', 'portfolio.resume_approved' => true]);

    $this->get('/pt-BR')->assertOk()->assertDontSee('Baixar currículo');
    $this->get('/resume')->assertNotFound();
});

it('rejects malformed emails and profile URLs without suppressing approved profile channels', function () {
    config([
        'portfolio.email' => 'person@example.org\r\nBcc:attacker@example.org',
        'portfolio.linkedin' => 'https://www.linkedin.com.evil.example/in/jackson-severino-da-rocha/',
        'portfolio.github' => 'https://github.com/jacksonsr451?redirect=attacker.example',
    ]);

    $this->get('/pt-BR/contact')
        ->assertOk()
        ->assertDontSee('mailto:', false)
        ->assertDontSee('href="https://www.linkedin.com.evil.example', false)
        ->assertDontSee('href="https://github.com/jacksonsr451?', false);
});

it('shows a project detail with structured case-study sections', function () {
    $this->get('/en/projects/eventflow')->assertOk()->assertSee('Technology stack')->assertSee('Problem statement')->assertSee('Architecture')->assertSee('Testing strategy')->assertSee('ADR-001');
});

it('returns not found for unknown or malformed project slugs', function () {
    $this->get('/en/projects/does-not-exist')->assertNotFound();
    $this->get('/en/projects/Bad_Slug')->assertNotFound();
});

it('lists published articles and renders markdown content safely', function () {
    $this->get('/en/writing')->assertOk()->assertSee('Designing idempotent API operations');
    $this->get('/en/writing/idempotent-apis')->assertOk()->assertSee('check-then-insert');
    $this->get('/en/writing/missing-article')->assertNotFound();
});

it('excludes articles with invalid front matter from listings and detail routes', function () {
    $fixtures = [
        'draft-status' => "slug: coverage-draft\ntitle: Coverage draft\ndescription: Not published.\ndate: 2026-10-11\nstatus: draft",
        'invalid-slug' => "slug: Coverage_Invalid\ntitle: Coverage invalid slug\ndescription: Invalid identifier.\ndate: 2026-10-11\nstatus: published",
        'invalid-date' => "slug: coverage-invalid-date\ntitle: Coverage invalid date\ndescription: Invalid calendar date.\ndate: 2026-02-30\nstatus: published",
        'missing-metadata' => "slug: coverage-missing-description\ntitle: Coverage missing description\ndate: 2026-10-11\nstatus: published",
    ];
    $paths = [];

    foreach ($fixtures as $name => $frontMatter) {
        $path = resource_path("content/articles/en/coverage-{$name}.md");
        $paths[] = $path;
        file_put_contents($path, "---\n{$frontMatter}\n---\nThis content must not be published.\n");
    }

    try {
        $listing = $this->get('/en/writing')->assertOk();

        foreach (['Coverage draft', 'Coverage invalid slug', 'Coverage invalid date', 'Coverage missing description'] as $title) {
            $listing->assertDontSee($title);
        }

        foreach (['coverage-draft', 'Coverage_Invalid', 'coverage-invalid-date', 'coverage-missing-description'] as $slug) {
            $this->get("/en/writing/{$slug}")->assertNotFound();
        }
    } finally {
        foreach ($paths as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
});

it('strips raw HTML and unsafe links from published Markdown', function () {
    $slug = 'markdown-security-'.Str::uuid();
    $path = resource_path("content/articles/en/{$slug}.md");
    $content = <<<MARKDOWN
    ---
    slug: {$slug}
    title: <script>alert(1)</script>
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
        $this->get("/en/writing/{$slug}")
            ->assertOk()
            ->assertSee('Safe article text')
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>', false)
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
    $response = $this->get('/en')->assertOk()
        ->assertSee('rel="canonical"', false)
        ->assertSee('application/ld+json', false)
        ->assertSee('og:title', false)
        ->assertSee('hreflang="pt-BR"', false)
        ->assertSee('hreflang="en"', false)
        ->assertSee('hreflang="x-default"', false);

    preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $response->getContent(), $structuredData);
    expect(json_decode(trim($structuredData[1] ?? ''), true))
        ->toMatchArray(['@context' => 'https://schema.org', '@type' => 'Person', 'inLanguage' => 'en']);
});

it('serves a sitemap containing public routes and content', function () {
    $response = $this->get('/sitemap.xml')->assertOk()->assertHeader('content-type', 'application/xml; charset=UTF-8')->assertSee('/pt-BR/projects/eventflow')->assertSee('/en/projects/eventflow')->assertSee('/pt-BR/writing/idempotent-apis')->assertSee('/en/writing/idempotent-apis')->assertSee('hreflang="x-default"', false);

    expect(simplexml_load_string($response->getContent()))->toBeInstanceOf(SimpleXMLElement::class);
});

it('serves the framework health endpoint', function () {
    $this->get('/up')->assertOk();
});

it('serves robots rules with an absolute sitemap address', function () {
    $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: '.url('/sitemap.xml'));
});
