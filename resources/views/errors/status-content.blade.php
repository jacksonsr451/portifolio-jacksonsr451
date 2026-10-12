<section class="section-wrap page-intro" role="status">
    <p class="eyebrow">{{ $statusCode }}</p>
    <h1>{{ $heading }}</h1>
    <p class="lead">{{ $message }}</p>
    <a class="button button-primary" href="{{ route('localized.home', ['locale' => in_array(app()->getLocale(), config('i18n.supported'), true) ? app()->getLocale() : config('i18n.default')]) }}">{{ __('ui.errors.home') }}</a>
</section>
