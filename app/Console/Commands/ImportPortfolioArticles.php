<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportPortfolioArticles extends Command
{
    protected $signature = 'portfolio:import-articles {--dry-run : Report changes without writing}';

    protected $description = 'Import legacy Writing Markdown into the database idempotently.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $counts = ['created' => 0, 'translations' => 0, 'skipped' => 0, 'inconsistent' => 0];

        foreach (array_keys(config('i18n.locales', [])) as $locale) {
            $directory = resource_path('content/articles/'.$locale);
            if (! File::isDirectory($directory)) {
                continue;
            }
            foreach (File::files($directory) as $file) {
                if ($file->getExtension() !== 'md') {
                    continue;
                }
                $data = $this->parse($file->getContents());
                if ($data === null) {
                    $counts['inconsistent']++;
                    $this->warn("Invalid front matter: {$file->getFilename()}");

                    continue;
                }
                $article = Article::query()->where('slug', $data['slug'])->first();
                if ($article === null) {
                    $counts['created']++;
                    if (! $dryRun) {
                        $article = Article::query()->create(['slug' => $data['slug']]);
                    }
                }
                if ($dryRun) {
                    continue;
                }
                if ($article->translations()->where('locale', $locale)->exists()) {
                    $counts['skipped']++;

                    continue;
                }
                $article->translations()->create([
                    'locale' => $locale,
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'body_markdown' => $data['body'],
                    'seo_title' => $data['title'],
                    'seo_description' => $data['description'],
                    'published_on' => $data['date'],
                    'editorial_status' => $data['status'],
                    'published_at' => $data['status'] === 'published' ? $data['date'].' 00:00:00' : null,
                ]);
                $counts['translations']++;
            }
        }

        $this->table(['Metric', 'Count'], collect($counts)->map(fn ($value, $key) => [$key, $value])->values()->all());

        return $counts['inconsistent'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    /** @return array{slug:string,title:string,description:string,date:string,status:string,body:string}|null */
    private function parse(string $content): ?array
    {
        if (preg_match('/\A---\R(.*?)\R---\R(.*)\z/s', $content, $parts) !== 1) {
            return null;
        }
        $metadata = [];
        foreach (preg_split('/\R/', $parts[1]) ?: [] as $line) {
            if (preg_match('/^(slug|title|description|date|status):\s*(.+)$/', $line, $match) === 1) {
                $metadata[$match[1]] = trim($match[2], " \t\"'");
            }
        }
        $date = $metadata['date'] ?? '';
        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $validDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1 && $parsed instanceof \DateTimeImmutable && $parsed->format('Y-m-d') === $date;
        if (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $metadata['slug'] ?? '') !== 1 || ! $validDate || ! in_array($metadata['status'] ?? '', ['draft', 'published', 'archived'], true) || trim($parts[2]) === '' || ! isset($metadata['title'], $metadata['description'])) {
            return null;
        }

        return [...$metadata, 'body' => trim($parts[2])];
    }
}
