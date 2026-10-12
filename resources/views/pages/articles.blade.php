@extends('layouts.app')
@section('content')
<section class="section-wrap page-intro">
    <p class="eyebrow">{{ __('ui.writing.eyebrow') }}</p>
    <h1>{{ __('ui.writing.title') }} <em>{{ __('ui.writing.title_em') }}</em></h1>
    <p class="lead">{{ __('ui.writing.intro') }}</p>
</section>
<section class="section-wrap article-list">
    @forelse($articles as $article)
        <a class="article-row" href="{{ route('localized.articles.show', ['locale' => app()->getLocale(), 'slug' => $article['slug']]) }}">
            <time datetime="{{ $article['date'] }}">{{ \Illuminate\Support\Carbon::parse($article['date'])->locale(app()->getLocale())->translatedFormat(__('ui.article.month_format')) }}</time>
            <div><h2>{{ $article['title'] }}</h2><p>{{ $article['description'] }}</p></div><span aria-hidden="true">↗</span>
        </a>
    @empty
        <p>{{ __('ui.writing.empty') }}</p>
    @endforelse
</section>
@endsection
