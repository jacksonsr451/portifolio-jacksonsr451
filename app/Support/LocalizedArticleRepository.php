<?php

namespace App\Support;

use App\Models\Article;

class LocalizedArticleRepository
{
    /** @return list<array<string, mixed>> */
    public function published(string $locale): array
    {
        abort_unless(app(SupportedLocales::class)->supports($locale), 404);

        return Article::query()
            ->with(['translations' => fn ($query) => $query->where('locale', $locale)->where('editorial_status', 'published')])
            ->whereNull('archived_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Article $article) => $this->toArray($article))
            ->filter()->sortByDesc('date')->values()->all();
    }

    /** @return array<string, mixed>|null */
    public function find(string $locale, string $slug): ?array
    {
        if (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) !== 1) {
            return null;
        }

        return collect($this->published($locale))->firstWhere('slug', $slug);
    }

    /** @return array<string, mixed>|null */
    private function toArray(Article $article): ?array
    {
        $translation = $article->translations->first();
        if ($translation === null) {
            return null;
        }

        $publishedOn = $translation->getAttribute('published_on');
        $date = $publishedOn instanceof \DateTimeInterface ? $publishedOn->format('Y-m-d') : '';

        return [
            'slug' => $article->slug,
            'title' => (string) $translation->getAttribute('title'),
            'description' => (string) $translation->getAttribute('description'),
            'date' => $date,
            'status' => (string) $translation->getAttribute('editorial_status'),
            'body' => (string) $translation->getAttribute('body_markdown'),
            'seo_title' => (string) $translation->getAttribute('seo_title'),
            'seo_description' => (string) $translation->getAttribute('seo_description'),
            'cover_alt' => $translation->getAttribute('cover_alt'),
        ];
    }
}
