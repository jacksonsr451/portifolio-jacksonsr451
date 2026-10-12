@extends('layouts.app')
@section('content')
<section class="section-wrap page-intro">
    <p class="eyebrow">{{ __('ui.projects.eyebrow') }}</p>
    <h1>{{ __('ui.projects.title') }} <em>{{ __('ui.projects.title_em') }}</em></h1>
    <p class="lead">{{ __('ui.projects.intro') }}</p>
</section>
<section class="section-wrap project-grid project-list">
    @foreach($projects as $project)
        <a class="project-card" href="{{ route('localized.projects.show', ['locale' => app()->getLocale(), 'slug' => $project['slug']]) }}">
            <div class="card-top"><span class="project-index">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><span aria-hidden="true">↗</span></div>
            <h2>{{ $project['title'] }}</h2><p>{{ $project['summary'] }}</p>
            @if($project['stack'])<div class="tags">@foreach($project['stack'] as $tag)<span>{{ $tag }}</span>@endforeach</div>@endif
            <small>{{ $project['status'] }}</small>
        </a>
    @endforeach
</section>
@endsection
