@extends('layouts.app')
@section('content')
<article class="section-wrap article-detail">
    <a class="text-link back-link" href="{{ route('localized.articles.index', ['locale' => app()->getLocale()]) }}">{{ __('ui.actions.back_writing') }}</a>
    <time datetime="{{ $article['date'] }}">{{ \Illuminate\Support\Carbon::parse($article['date'])->locale(app()->getLocale())->translatedFormat(__('ui.article.date_format')) }}</time>
    <h1>{{ $article['title'] }}</h1>
    <p class="lead">{{ $article['description'] }}</p>
    <div class="prose">{!! $article['html'] !!}</div>
</article>
@endsection