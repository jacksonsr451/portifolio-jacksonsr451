<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;

class ImportPortfolioProjects extends Command
{
    protected $signature = 'portfolio:import-projects {--dry-run : Report changes without writing}';

    protected $description = 'Import the verified project catalog into the database idempotently.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $counts = ['created' => 0, 'updated' => 0, 'translations' => 0, 'inconsistent' => 0];
        $locales = array_keys(config('i18n.locales', []));

        foreach ((array) config('portfolio.projects', []) as $order => $source) {
            $translations = [];
            foreach ($locales as $locale) {
                $editorial = require lang_path($locale.'/portfolio.php');
                if (isset($editorial['projects'][$source['slug']]) && is_array($editorial['projects'][$source['slug']])) {
                    $translations[$locale] = $editorial['projects'][$source['slug']];
                }
            }
            if ($translations === []) {
                $counts['inconsistent']++;
                $this->warn("No translations found for {$source['slug']}");

                continue;
            }

            $attributes = [
                'status_key' => $source['status_key'] ?? null,
                'stack' => $source['stack'] ?? [],
                'links' => ['repository' => $source['repository'] ?? null, 'demo' => $source['demo'] ?? null],
                'evidence' => $source['sources'] ?? [],
                'display_order' => $order,
                'featured' => (bool) ($source['featured'] ?? false),
            ];
            $project = Project::query()->where('slug', $source['slug'])->first();
            if ($project === null) {
                $counts['created']++;
                if (! $dryRun) {
                    $project = Project::query()->create(['slug' => $source['slug'], ...$attributes]);
                }
            } else {
                $counts['updated']++;
            }
            if ($dryRun) {
                continue;
            }

            foreach ($translations as $locale => $translation) {
                $translationModel = $project->translations()->firstOrNew(['locale' => $locale]);
                if (! $translationModel->exists) {
                    $translationModel->fill([...$translation, 'editorial_status' => 'published', 'published_at' => now()]);
                    $translationModel->save();
                    $counts['translations']++;
                }
            }
        }

        $this->table(['Metric', 'Count'], collect($counts)->map(fn ($value, $key) => [$key, $value])->values()->all());

        return $counts['inconsistent'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
