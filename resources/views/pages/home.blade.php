@extends('layouts.app')
@section('content')
<section class="hero section-wrap">
    <p class="eyebrow"><span class="status-dot"></span> {{ __('ui.home.eyebrow') }}</p>
    <h1>{{ __('ui.home.title') }}<br><em>{{ __('ui.home.title_em') }}</em></h1>
    <p class="hero-copy">{{ __('ui.home.intro') }}</p>
    <div class="actions">
        <a class="button button-primary" href="{{ route('localized.projects.index', ['locale' => app()->getLocale()]) }}">{{ __('ui.actions.explore_projects') }} <span aria-hidden="true">↗</span></a>
        <a class="button button-quiet" href="{{ route('localized.contact', ['locale' => app()->getLocale()]) }}">{{ __('ui.actions.contact') }}</a>
        @if($resumeAvailable)<a class="button button-quiet" href="{{ route('resume.download') }}">{{ __('ui.actions.download_resume') }}</a>@endif
    </div>
    <p class="hero-note">{{ __('ui.home.experience') }}</p>
</section>
<section class="section-wrap section-block">
    <div class="section-heading"><div><p class="eyebrow">{{ __('ui.home.selected_eyebrow') }}</p><h2>{{ __('ui.home.selected_title') }}</h2></div><a class="text-link" href="{{ route('localized.projects.index', ['locale' => app()->getLocale()]) }}">{{ __('ui.actions.all_projects') }} ↗</a></div>
    <div class="project-grid">
        @foreach($projects as $project)
            <a class="project-card" href="{{ route('localized.projects.show', ['locale' => app()->getLocale(), 'slug' => $project['slug']]) }}">
                <div class="card-top"><span class="project-index">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><span aria-hidden="true">↗</span></div>
                <h3>{{ $project['title'] }}</h3><p>{{ $project['summary'] }}</p>
                @if($project['stack'])<div class="tags">@foreach($project['stack'] as $tag)<span>{{ $tag }}</span>@endforeach</div>@endif
                <small>{{ $project['status'] }}</small>
            </a>
        @endforeach
    </div>
</section>
<section class="section-wrap section-block split-block">
    <div><p class="eyebrow">{{ __('ui.home.how_eyebrow') }}</p><h2>{{ __('ui.home.how_title') }}</h2></div>
    <div><p>{{ __('ui.home.how_text') }}</p><a class="text-link" href="{{ route('localized.about', ['locale' => app()->getLocale()]) }}">{{ __('ui.actions.about_approach') }} ↗</a></div>
</section>
<section class="section-wrap section-block open-source-preview">
    <div><p class="eyebrow">{{ __('ui.home.open_source_eyebrow') }}</p><h2>{{ __('ui.home.open_source_title') }}</h2>
        <ul aria-label="{{ __('ui.home.merged_contributions') }}">
            @foreach($contributions as $item)
                <li><strong>{{ $item['organization'] }} / {{ $item['repository'] }}</strong> — {{ $item['scope'] }} <a href="{{ $item['pull_request_url'] }}" target="_blank" rel="noopener noreferrer">{{ __('ui.actions.pull_request', ['number' => $item['pull_request']]) }} <span class="sr-only">{{ __('ui.navigation.new_tab') }}</span></a></li>
            @endforeach
        </ul>
        <a class="text-link" href="{{ route('localized.open-source', ['locale' => app()->getLocale()]) }}">{{ __('ui.actions.all_open_source') }} ↗</a>
    </div><span class="outline-mark" aria-hidden="true">{ }</span>
</section>
<section class="section-wrap contact-band"><p class="eyebrow">{{ __('ui.home.contact_eyebrow') }}</p><h2>{{ __('ui.home.contact_title') }}</h2><a class="button button-primary" href="{{ route('localized.contact', ['locale' => app()->getLocale()]) }}">{{ __('ui.actions.contact_details') }} ↗</a></section>
@endsection
