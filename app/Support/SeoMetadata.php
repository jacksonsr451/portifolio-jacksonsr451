<?php

namespace App\Support;

class SeoMetadata
{
    public function isIndexable(): bool
    {
        return in_array(config('app.env'), ['production', 'testing'], true);
    }

    public function canonicalUrl(): string
    {
        $origin = rtrim((string) config('app.url'), '/');
        $path = parse_url(url()->current(), PHP_URL_PATH) ?: '/';
        $url = $origin.$path;

        if (config('app.env') === 'production') {
            $parts = parse_url($url);

            abort_unless(($parts['scheme'] ?? null) === 'https', 500);
            abort_unless(! in_array(strtolower((string) ($parts['host'] ?? '')), ['localhost', '127.0.0.1', '::1'], true), 500);
        }

        return $url;
    }

    public function robots(): string
    {
        return $this->isIndexable() ? 'index, follow' : 'noindex, nofollow';
    }

    /** @param array<string, mixed> $data */
    public function json(array $data): string
    {
        return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
