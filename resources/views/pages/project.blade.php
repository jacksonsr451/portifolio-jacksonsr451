@extends('layouts.app')
@section('content')
<article class="section-wrap case-study">
    <a class="text-link back-link" href="{{ route('localized.projects.index', ['locale' => app()->getLocale()]) }}">{{ __('ui.actions.back_projects') }}</a>
    <p class="eyebrow">{{ __('ui.project.eyebrow') }}</p>
    <h1>{{ $project['title'] }}</h1><p class="lead">{{ $project['summary'] }}</p>
    <div class="case-grid">
        @if($project['stack'])
            <section><h2>{{ __('ui.projects.technology_stack') }}</h2><div class="tags">@foreach($project['stack'] as $tag)<span>{{ $tag }}</span>@endforeach</div></section>
        @endif
        @foreach(['problem' => 'problem', 'context' => 'context', 'role' => 'role', 'architecture' => 'architecture', 'decisions' => 'decisions', 'tradeoffs' => 'tradeoffs', 'testing' => 'testing', 'challenges' => 'challenges', 'evidence' => 'evidence'] as $key => $field)
            <section><h2>{{ __('ui.project.'.$key) }}</h2><p>{{ $project[$field] }}</p></section>
        @endforeach
        @if($project['sources'])
            <section><h2>{{ __('ui.project.sources') }}</h2><ul>@foreach($project['sources'] as $source)<li><a href="{{ $source['url'] }}" rel="noopener noreferrer">{{ $source['label'] }} ↗</a></li>@endforeach</ul></section>
        @endif
    </div>
    <div class="actions">
        @if($project['repository'])<a class="button button-primary" href="{{ $project['repository'] }}" rel="noopener noreferrer">{{ __('ui.actions.repository') }} ↗</a>@endif
        @if($project['demo'])<a class="button button-quiet" href="{{ $project['demo'] }}" rel="noopener noreferrer">{{ __('ui.actions.demo') }} ↗</a>@endif
    </div>
</article>
@endsection
