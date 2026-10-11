<?php

it('has unique valid project slugs and required case-study fields', function () {
    $projects = config('portfolio.projects');
    $slugs = array_column($projects, 'slug');

    expect($slugs)->toHaveCount(count(array_unique($slugs)));
    foreach ($projects as $project) {
        expect($project)->toHaveKeys(['slug', 'title', 'summary', 'status', 'stack', 'problem', 'context', 'role', 'architecture', 'decisions', 'tradeoffs', 'testing', 'challenges', 'evidence']);
        expect($project['slug'])->toMatch('/^[a-z0-9-]+$/');
    }
});

it('validates article front matter and unique published identifiers', function () {
    $files = glob(resource_path('content/articles/*.md'));
    expect($files)->not->toBeEmpty();

    $slugs = [];
    foreach ($files as $file) {
        $content = file_get_contents($file);
        expect($content)->toBeString()->toStartWith("---\n");

        preg_match('/\A---\R(.*?)\R---\R(.+)\z/s', $content, $parts);
        $metadata = [];
        foreach (preg_split('/\R/', $parts[1] ?? '') as $line) {
            if (preg_match('/^(slug|title|description|date|status):\s*(.+)$/', $line, $match)) {
                $metadata[$match[1]] = trim($match[2], " \t\"'");
            }
        }

        expect($metadata)->toHaveKeys(['slug', 'title', 'description', 'date', 'status']);
        expect($metadata['slug'])->toMatch('/^[a-z0-9]+(?:-[a-z0-9]+)*$/');
        expect($metadata['status'])->toBeIn(['draft', 'published']);
        expect($metadata['date'])->toMatch('/^\d{4}-\d{2}-\d{2}$/');
        expect(DateTimeImmutable::createFromFormat('!Y-m-d', $metadata['date']))->toBeInstanceOf(DateTimeImmutable::class);
        $slugs[] = $metadata['slug'];
    }

    expect($slugs)->toHaveCount(count(array_unique($slugs)));
});
