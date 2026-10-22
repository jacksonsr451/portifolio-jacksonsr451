<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $article->exists ? __('admin.edit_article') : __('admin.new_article') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<main class="section-wrap">
    <h1>{{ $article->exists ? __('admin.edit_article') : __('admin.new_article') }}</h1>

    @if ($errors->any())
        <ul role="alert">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    @if (session('status'))
        <p role="status">{{ session('status') }}</p>
    @endif

    <form method="post" action="{{ $article->exists ? route('admin.articles.update', $article) : route('admin.articles.store') }}">
        @csrf
        @if ($article->exists)
            @method('PUT')
        @endif

        <label>
            {{ __('admin.slug') }}
            <input name="slug" value="{{ old('slug', $article->slug) }}" required>
        </label>

        @foreach ($locales as $locale)
            @php($translation = $article->translations->firstWhere('locale', $locale))
            <fieldset>
                <legend>{{ __('admin.locale') }}: {{ $locale }}</legend>

                @foreach (['title', 'description', 'seo_title', 'seo_description', 'cover_alt', 'published_on'] as $field)
                    <label>
                        {{ __('admin.'.$field) }}
                        <input name="translations[{{ $locale }}][{{ $field }}]" value="{{ old("translations.$locale.$field", $translation?->{$field} ?? '') }}">
                    </label>
                @endforeach

                <label>
                    {{ __('admin.body_markdown') }}
                    <textarea name="translations[{{ $locale }}][body_markdown]" rows="18">{{ old("translations.$locale.body_markdown", $translation?->body_markdown ?? '') }}</textarea>
                </label>

                <label>
                    {{ __('admin.editorial_status') }}
                    <select name="translations[{{ $locale }}][editorial_status]">
                        @foreach (['draft', 'published', 'archived'] as $status)
                            <option value="{{ $status }}" @selected(old("translations.$locale.editorial_status", $translation?->editorial_status ?? 'draft') === $status)>{{ __('admin.'.$status) }}</option>
                        @endforeach
                    </select>
                </label>
            </fieldset>
        @endforeach

        <button type="submit">{{ __('admin.save') }}</button>
        <a href="{{ route('admin.articles.index') }}">{{ __('admin.cancel') }}</a>
    </form>
</main>
</body>
</html>
