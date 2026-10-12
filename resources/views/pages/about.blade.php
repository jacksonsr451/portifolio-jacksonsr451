@extends('layouts.app')
@section('content')
<section class="section-wrap page-intro">
    <p class="eyebrow">{{ __('ui.about.eyebrow') }}</p>
    <h1>{{ __('ui.about.title') }} <em>{{ __('ui.about.title_em') }}</em></h1>
    <p class="lead">{{ __('ui.about.intro') }}</p>
</section>
<section class="section-wrap prose-layout">
    <article class="prose">
        <h2>{{ __('ui.about.focus_title') }}</h2><p>{{ __('ui.about.focus') }}</p><p>{{ __('ui.about.educator') }}</p>
        <h2>{{ __('ui.about.principles_title') }}</h2>
        <ul>@foreach(__('ui.about.principles') as $principle)<li>{{ $principle }}</li>@endforeach</ul>
        <h2>{{ __('ui.about.experience_title') }}</h2><p>{{ __('ui.about.experience') }}</p>
    </article>
    <aside class="side-note"><p class="eyebrow">{{ __('ui.about.toolkit') }}</p><p>@foreach(__('ui.about.toolkit_lines') as $line){{ $line }}@if(! $loop->last)<br>@endif @endforeach</p></aside>
</section>
@endsection
