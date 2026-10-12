<!doctype html>
<html lang="{{ app()->getLocale() }}" data-theme="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $description ?? __('ui.seo.home_description') }}">
    <meta name="theme-color" content="#101413">
    @hasSection('robots')
    <meta name="robots" content="@yield('robots')">@endif
    <link rel="canonical" href="{{ url()->current() }}">
    @foreach($localeAlternates as $language => $alternateUrl)
    <link rel="alternate" hreflang="{{ $language }}" href="{{ $alternateUrl }}">
    @endforeach
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title ?? __('ui.seo.home_title') }}">
    <meta property="og:description" content="{{ $description ?? __('ui.seo.home_description') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="{{ $localeOptions[app()->getLocale()]['og_locale'] ?? $localeOptions[$defaultLocale]['og_locale'] }}">
    @foreach($localeAlternates as $alternateLocale => $alternateUrl)
    @if($alternateLocale !== app()->getLocale() && $alternateLocale !== 'x-default')
    <meta property="og:locale:alternate" content="{{ $localeOptions[$alternateLocale]['og_locale'] }}">
    @endif
    @endforeach
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $title ?? __('ui.seo.home_title') }}">
    <meta name="twitter:description" content="{{ $description ?? __('ui.seo.home_description') }}">
    <title>{{ $title ?? __('ui.seo.home_title') }}</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php($structuredData = json_encode(['@context' => 'https://schema.org', '@type' => 'Person', 'name' => config('portfolio.name'), 'jobTitle' => __('ui.site.json_job_title'), 'inLanguage' => app()->getLocale(), 'url' => url()->current(), 'sameAs' => array_values(array_filter($profileLinks)), 'knowsAbout' => ['Python', 'FastAPI', 'Java', 'Spring Boot', 'REST APIs', 'PostgreSQL', 'Docker']], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES))
    <script type="application/ld+json">
        <?php echo $structuredData; ?>
    </script>
</head>

<body>
    <a class="skip-link" href="#main">{{ __('ui.navigation.skip') }}</a>
    <header class="site-header">
        <a class="wordmark" href="{{ route('localized.home', ['locale' => app()->getLocale()]) }}" aria-label="{{ config('portfolio.name') }} — {{ __('ui.navigation.home') }}">JS<span>.</span></a>
        <nav aria-label="{{ __('ui.navigation.label') }}">
            <a href="{{ route('localized.about', ['locale' => app()->getLocale()]) }}">{{ __('ui.navigation.about') }}</a>
            <a href="{{ route('localized.projects.index', ['locale' => app()->getLocale()]) }}">{{ __('ui.navigation.projects') }}</a>
            <a href="{{ route('localized.open-source', ['locale' => app()->getLocale()]) }}">{{ __('ui.navigation.open_source') }}</a>
            <a href="{{ route('localized.articles.index', ['locale' => app()->getLocale()]) }}">{{ __('ui.navigation.writing') }}</a>
            <a href="{{ route('localized.contact', ['locale' => app()->getLocale()]) }}">{{ __('ui.navigation.contact') }}</a>
        </nav>
        <div class="language-selector flex shrink-0 items-center gap-2" role="group" aria-label="{{ __('ui.navigation.language_switch') }}">
            @foreach($localeOptions as $supportedLocale => $localeDetails)
            <a href="{{ $localeSwitchUrls[$supportedLocale] }}" lang="{{ $supportedLocale }}" hreflang="{{ $supportedLocale }}" @if($supportedLocale===app()->getLocale()) aria-current="page" class="font-semibold text-[var(--accent)] underline underline-offset-4" @else class="text-[var(--muted)]" @endif>{{ $localeDetails['name'] }}</a>
            @endforeach
        </div>
        <button class="theme-toggle" type="button" aria-label="{{ __('ui.navigation.theme_toggle') }}">◐ <span>{{ __('ui.navigation.theme') }}</span></button>
    </header>
    <main id="main">@yield('content')</main>
    <footer class="site-footer">
        <a class="wordmark" href="{{ route('localized.home', ['locale' => app()->getLocale()]) }}" aria-label="{{ config('portfolio.name') }}">JS<span>.</span></a>
        <p>{{ __('ui.footer.statement') }}</p>
        <ul aria-label="{{ __('ui.navigation.profiles') }}">
            @if($profileLinks['linkedin'])<li><a href="{{ $profileLinks['linkedin'] }}" target="_blank" rel="noopener noreferrer">LinkedIn<span class="sr-only"> {{ __('ui.navigation.new_tab') }}</span></a></li>@endif
            @if($profileLinks['github'])<li><a href="{{ $profileLinks['github'] }}" target="_blank" rel="noopener noreferrer">GitHub<span class="sr-only"> {{ __('ui.navigation.new_tab') }}</span></a></li>@endif
        </ul>
        <a href="{{ route('sitemap') }}">{{ __('ui.footer.sitemap') }}</a>
        <small>© {{ date('Y') }} {{ config('portfolio.name') }}</small>
    </footer>
</body>

</html>
