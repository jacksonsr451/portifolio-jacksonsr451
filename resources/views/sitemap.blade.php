<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
    @foreach($entries as $entry)
    <url>
        <loc>{{ $entry['loc'] }}</loc>
        @foreach($entry['alternates'] as $locale => $alternateUrl)
        <xhtml:link rel="alternate" hreflang="{{ $locale }}" href="{{ $alternateUrl }}" />@endforeach
    </url>
    @endforeach
</urlset>
