<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class LocalizedArticleRepository
{
    /** @return list<array{slug:string,title:string,description:string,date:string,status:string,body:string}> */
    public function published(string $locale): array
    {
        if (! in_array($locale, config('i18n.supported'), true)) {
            return [];
        }

        $directory = resource_path('content/articles/'.$locale);
        if (! File::isDirectory($directory)) {
            return [];
        }

        return collect(File::files($directory))
            ->filter(fn ($file) => $file->getExtension() === 'md')
            ->map(fn ($file) => $this->parse($file->getContents()))
            ->filter(fn ($article) => $article !== null)
            ->sortByDesc('date')->values()->all();
    }

    /** @return array{slug:string,title:string,description:string,date:string,status:string,body:string}|null */
    public function find(string $locale, string $slug): ?array
    {
        if (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) !== 1) {
            return null;
        }

        foreach ($this->published($locale) as $article) {
            if ($article['slug'] === $slug) {
                return $article;
            }
        }

        return null;
    }

    /** @return array{slug:string,title:string,description:string,date:string,status:string,body:string}|null */
    private function parse(string $content): ?array
    {
        if (preg_match('/\A---\R(.*?)\R---\R(.*)\z/s', $content, $parts) !== 1) {
            return null;
        }

        $metadata = [];
        foreach (preg_split('/\R/', $parts[1]) as $line) {
            if (preg_match('/^(slug|title|description|date|status):\s*(.+)$/', $line, $match) === 1) {
                $metadata[$match[1]] = trim($match[2], " \t\"'");
            }
        }

        $date = $metadata['date'] ?? '';
        $parsedDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $validDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1
            && $parsedDate instanceof \DateTimeImmutable
            && $parsedDate->format('Y-m-d') === $date;

        if (($metadata['status'] ?? '') !== 'published'
            || preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $metadata['slug'] ?? '') !== 1
            || ! $validDate
            || ! isset($metadata['title'], $metadata['description'])) {
            return null;
        }

        return [...$metadata, 'body' => trim($parts[2])];
    }
}
