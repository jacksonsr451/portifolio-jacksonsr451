<section class="section-wrap page-intro" role="status">
    <p class="eyebrow">{{ $statusCode }}</p>
    <h1>{{ $heading }}</h1>
    <p class="lead">{{ $message }}</p>
    <a class="button button-primary" href="{{ route('localized.home', ['locale' => app(App\Support\SupportedLocales::class)->supports(app()->getLocale()) ? app()->getLocale() : $defaultLocale]) }}">{{ __('ui.errors.home') }}</a>
</section>
