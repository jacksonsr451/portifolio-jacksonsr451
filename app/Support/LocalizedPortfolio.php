<?php

namespace App\Support;

class LocalizedPortfolio
{
    /** @return list<array<string, mixed>> */
    public function projects(string $locale): array
    {
        $translations = $this->translations($locale);
        $interfaceTranslations = require lang_path($locale.'/ui.php');
        $projects = [];

        foreach (config('portfolio.projects') as $project) {
            $editorial = $translations['projects'][$project['slug']] ?? null;

            if (! is_array($editorial) || array_diff($this->projectTextKeys(), array_keys($editorial)) !== []) {
                continue;
            }

            $sources = [];
            foreach ($project['sources'] as $source) {
                $label = $interfaceTranslations['source_labels'][$source['label']] ?? null;
                if (! is_string($label)) {
                    continue 2;
                }
                $sources[] = [...$source, 'label' => $label];
            }

            $projects[] = [...$project, ...$editorial, 'sources' => $sources];
        }

        return $projects;
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
        abort_unless(in_array($locale, config('i18n.supported'), true), 404);

        return require lang_path($locale.'/portfolio.php');
    }

    /** @return list<string> */
    private function projectTextKeys(): array
    {
        return ['title', 'summary', 'status', 'problem', 'context', 'role', 'architecture', 'decisions', 'tradeoffs', 'testing', 'challenges', 'evidence'];
    }
}
