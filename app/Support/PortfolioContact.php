<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class PortfolioContact
{
    public function email(): ?string
    {
        $email = trim((string) config('portfolio.email', ''));

        return filter_var($email, FILTER_VALIDATE_EMAIL) === false ? null : $email;
    }

    /** @return array{linkedin:?string,github:?string} */
    public function profileLinks(): array
    {
        return [
            'linkedin' => $this->profileUrl(config('portfolio.linkedin'), 'www.linkedin.com', '/in/jackson-severino-da-rocha/'),
            'github' => $this->profileUrl(config('portfolio.github'), 'github.com', '/jacksonsr451'),
        ];
    }

    public function personStructuredData(): HtmlString
    {
        return new HtmlString(json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => config('portfolio.name'),
            'jobTitle' => __('ui.site.json_job_title'),
            'inLanguage' => app()->getLocale(),
            'url' => url()->current(),
            'sameAs' => array_values(array_filter($this->profileLinks())),
            'knowsAbout' => ['Python', 'FastAPI', 'Java', 'Spring Boot', 'REST APIs', 'PostgreSQL', 'Docker'],
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    public function resumePath(): ?string
    {
        if (! filter_var(config('portfolio.resume_approved', false), FILTER_VALIDATE_BOOL)) {
            return null;
        }

        $relativePath = trim((string) config('portfolio.resume', ''));

        if (preg_match('~\Aresumes/[A-Za-z0-9_-]+(?:/[A-Za-z0-9_-]+)*\.pdf\z~iD', $relativePath) !== 1) {
            return null;
        }

        $disk = Storage::disk('local');

        if (! $disk->exists($relativePath)) {
            return null;
        }

        $root = realpath($disk->path(''));
        $path = realpath($disk->path($relativePath));

        if ($root === false
            || $path === false
            || ! str_starts_with($path, $root.DIRECTORY_SEPARATOR.'resumes'.DIRECTORY_SEPARATOR)
            || ! is_file($path)
            || ! is_readable($path)) {
            return null;
        }

        return @file_get_contents($path, false, null, 0, 5) === '%PDF-' ? $relativePath : null;
    }

    private function profileUrl(mixed $value, string $host, string $path): ?string
    {
        if (! is_string($value) || filter_var($value, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $parts = parse_url($value);

        if ($parts === false
            || strtolower($parts['scheme'] ?? '') !== 'https'
            || strtolower($parts['host'] ?? '') !== $host
            || ($parts['path'] ?? '') !== $path
            || isset($parts['port'])
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['query'])
            || isset($parts['fragment'])) {
            return null;
        }

        return $value;
    }
}
