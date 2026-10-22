<?php

namespace App\Support;

use App\Models\Project;

class LocalizedPortfolio
{
    /** @return list<array<string, mixed>> */
    public function projects(string $locale): array
    {
        abort_unless(app(SupportedLocales::class)->supports($locale), 404);

        return Project::query()->with(['translations' => fn ($query) => $query->where('locale', $locale)->where('editorial_status', 'published')])
            ->whereNull('archived_at')->orderBy('display_order')->orderBy('id')->get()
            ->map(fn (Project $project) => $this->toArray($project, $locale))->filter()->values()->all();
    }

    /** @return array<string, mixed>|null */
    public function project(string $locale, string $slug): ?array
    {
        foreach ($this->projects($locale) as $project) {
            if ($project['slug'] === $slug) {
                return $project;
            }
        }

        return null;
    }

    /** @return list<array<string, mixed>> */
    public function contributions(string $locale): array
    {
        $translations = $this->translations($locale);
        $contributions = [];

        foreach (config('portfolio.open_source') as $item) {
            $editorial = $translations['open_source'][$item['key']] ?? null;
            if (! is_array($editorial) || array_diff(['scope', 'participation', 'status'], array_keys($editorial)) !== []) {
                continue;
            }

            $contributions[] = [...$item, ...$editorial];
        }

        return $contributions;
    }

    /** @return array<string, mixed> */
    private function translations(string $locale): array
    {
        abort_unless(app(SupportedLocales::class)->supports($locale), 404);

        return require lang_path($locale.'/portfolio.php');
    }

    /** @return array<string, mixed>|null */
    private function toArray(Project $project, string $locale): ?array
    {
        $translation = $project->translations->first();
        if ($translation === null) {
            return null;
        }
        $labels = require lang_path($locale.'/ui.php');
        $evidence = $this->jsonArray($project->getRawOriginal('evidence'));
        $sources = collect($evidence)->map(function (array $source) use ($labels): array {
            return [...$source, 'label' => $labels['source_labels'][$source['label'] ?? ''] ?? ($source['label'] ?? '')];
        })->all();

        $links = $this->jsonArray($project->getRawOriginal('links'));

        return [...$project->only(['slug', 'status_key', 'stack', 'featured']), ...$translation->only($this->projectTextKeys()), 'repository' => $links['repository'] ?? null, 'demo' => $links['demo'] ?? null, 'sources' => $sources];
    }

    /** @return list<string> */
    private function projectTextKeys(): array
    {
        return ['title', 'summary', 'status', 'problem', 'context', 'role', 'architecture', 'decisions', 'tradeoffs', 'testing', 'challenges', 'evidence'];
    }

    /** @return array<int|string, mixed> */
    private function jsonArray(mixed $value): array
    {
        $decoded = is_string($value) ? json_decode($value, true) : null;

        return is_array($decoded) ? $decoded : [];
    }
}
