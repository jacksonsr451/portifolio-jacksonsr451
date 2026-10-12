<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

it('renders the homepage with professional positioning and selected projects', function () {
    $this->get('/')->assertOk()->assertSee('Backend Engineer')->assertSee('EventFlow')->assertSee('Jackson Severino da Rocha');
});

it('renders each public section', function (string $path) {
    $this->get($path)->assertOk();
})->with(['/about', '/projects', '/open-source', '/writing', '/contact']);

it('shows linked and correctly classified open-source contributions on the page and homepage', function () {
    $page = $this->get('/open-source')->assertOk();
    $page->assertSee('Eclipse Foundation / otterdog')
        ->assertSee('Issue #536')->assertSee('PR #765')
        ->assertSee('Merged')->assertSee('Open issue — discussion only')
        ->assertSee('https://github.com/eclipse-csi/otterdog/pull/779', false)
        ->assertSee('https://github.com/microsoft/PyRIT/issues/2748', false)
        ->assertSee('Completion PR #2989 (by another contributor)')
        ->assertDontSee('Verification required');

    $this->get('/')
        ->assertOk()
        ->assertSee('Contributions with evidence.')
        ->assertSee('PR #765')
        ->assertSee('PR #779')
        ->assertDontSee('Candidate contribution records');
});

it('omits invalid optional contribution links and escapes contribution content', function () {
    $contributions = config('portfolio.open_source');
    $contributions[0]['scope'] = '<script>alert("x")</script>';
    $contributions[0]['pull_request_url'] = 'javascript:alert(1)';
    config(['portfolio.open_source' => $contributions]);

    $this->get('/open-source')
        ->assertOk()
        ->assertSee('&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;', false)
        ->assertDontSee('<script>', false)
        ->assertDontSee('href="javascript:', false);
});

it('shows approved professional profile links and a valid configured email consistently', function () {
    config(['portfolio.email' => 'jackson@example.org']);

    $this->get('/contact')
        ->assertOk()
        ->assertSee('mailto:jackson@example.org', false)
        ->assertSee('Email jackson@example.org')
        ->assertSee('https://www.linkedin.com/in/jackson-severino-da-rocha/', false)
        ->assertSee('https://github.com/jacksonsr451', false)
        ->assertSee('rel="noopener noreferrer"', false);

    $this->get('/')
        ->assertOk()
        ->assertSee('LinkedIn')
        ->assertSee('GitHub')
        ->assertSee('href="'.route('contact').'"', false);
});

it('omits invalid or missing email and unsafe profile URLs', function () {
    config([
        'portfolio.email' => 'not-an-email',
        'portfolio.linkedin' => 'javascript:alert(1)',
        'portfolio.github' => 'https://attacker.example/jacksonsr451',
    ]);

    $this->get('/contact')
        ->assertOk()
        ->assertDontSee('mailto:', false)
        ->assertDontSee('Connect on LinkedIn')
        ->assertDontSee('View GitHub profile')
        ->assertSee('No professional contact channel is configured yet.')
        ->assertDontSee('href="javascript:', false);
});

it('keeps approved profiles available when the professional email is unset', function () {
    config(['portfolio.email' => null]);

    $this->get('/contact')
        ->assertOk()
        ->assertSee('Connect on LinkedIn')
        ->assertSee('View GitHub profile')
        ->assertDontSee('mailto:', false);
});

it('does not render a resume download while absent or unapproved', function () {
    config(['portfolio.resume' => '', 'portfolio.resume_approved' => false]);

    $this->get('/')->assertOk()->assertDontSee('Download résumé');
    $this->get('/contact')->assertOk()->assertDontSee('Download résumé');
    $this->get('/resume')->assertNotFound();
});

it('serves an explicitly approved PDF from private storage as a download', function () {
    Storage::fake('local');
    Storage::disk('local')->put('resumes/approved-cv.pdf', "%PDF-1.7\nApproved test fixture\n%%EOF");
    config(['portfolio.resume' => 'resumes/approved-cv.pdf', 'portfolio.resume_approved' => true]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Download résumé (PDF)')
        ->assertSee('href="'.route('resume.download').'"', false);

    $this->get('/contact')->assertOk()->assertSee('Download résumé (PDF)');

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

    $this->get('/')->assertOk()->assertDontSee('Download résumé');
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

    $this->get('/')->assertOk()->assertDontSee('Download résumé');
    $this->get('/resume')->assertNotFound();
});

it('rejects malformed emails and profile URLs without suppressing approved profile channels', function () {
    config([
        'portfolio.email' => 'person@example.org\r\nBcc:attacker@example.org',
        'portfolio.linkedin' => 'https://www.linkedin.com.evil.example/in/jackson-severino-da-rocha/',
        'portfolio.github' => 'https://github.com/jacksonsr451?redirect=attacker.example',
    ]);

    $this->get('/contact')
        ->assertOk()
        ->assertDontSee('mailto:', false)
        ->assertDontSee('href="https://www.linkedin.com.evil.example', false)
        ->assertDontSee('href="https://github.com/jacksonsr451?', false);
});

it('shows a project detail with structured case-study sections', function () {
    $this->get('/projects/eventflow')->assertOk()->assertSee('Technology stack')->assertSee('Problem statement')->assertSee('Architecture')->assertSee('Testing strategy')->assertSee('ADR-001');
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

it('excludes articles with invalid front matter from listings and detail routes', function () {
    $fixtures = [
        'draft-status' => "slug: coverage-draft\ntitle: Coverage draft\ndescription: Not published.\ndate: 2026-10-11\nstatus: draft",
        'invalid-slug' => "slug: Coverage_Invalid\ntitle: Coverage invalid slug\ndescription: Invalid identifier.\ndate: 2026-10-11\nstatus: published",
        'invalid-date' => "slug: coverage-invalid-date\ntitle: Coverage invalid date\ndescription: Invalid calendar date.\ndate: 2026-02-30\nstatus: published",
        'missing-metadata' => "slug: coverage-missing-description\ntitle: Coverage missing description\ndate: 2026-10-11\nstatus: published",
    ];
    $paths = [];

    foreach ($fixtures as $name => $frontMatter) {
        $path = resource_path("content/articles/coverage-{$name}.md");
        $paths[] = $path;
        file_put_contents($path, "---\n{$frontMatter}\n---\nThis content must not be published.\n");
    }

    try {
        $listing = $this->get('/writing')->assertOk();

        foreach (['Coverage draft', 'Coverage invalid slug', 'Coverage invalid date', 'Coverage missing description'] as $title) {
            $listing->assertDontSee($title);
        }

        foreach (['coverage-draft', 'Coverage_Invalid', 'coverage-invalid-date', 'coverage-missing-description'] as $slug) {
            $this->get("/writing/{$slug}")->assertNotFound();
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
    $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: '.url('/sitemap.xml'));
});
