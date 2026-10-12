@extends('layouts.app')
@section('content')
<section class="section-wrap page-intro contact-page">
    <p class="eyebrow">{{ __('ui.contact.eyebrow') }}</p>
    <h1>{{ __('ui.contact.title') }} <em>{{ __('ui.contact.title_em') }}</em></h1>
    <p class="lead">{{ __('ui.contact.intro') }}</p>

    @if($email)
        <p><a class="button button-primary" href="mailto:{{ $email }}">{{ __('ui.actions.email', ['address' => $email]) }}</a></p>
    @endif

    @if($profileLinks['linkedin'])
        <p><a class="button button-quiet" href="{{ $profileLinks['linkedin'] }}" target="_blank" rel="noopener noreferrer">{{ __('ui.actions.linkedin') }} <span aria-hidden="true">↗</span><span class="sr-only"> {{ __('ui.navigation.new_tab') }}</span></a></p>
    @endif

    @if($profileLinks['github'])
        <p><a class="button button-quiet" href="{{ $profileLinks['github'] }}" target="_blank" rel="noopener noreferrer">{{ __('ui.actions.github') }} <span aria-hidden="true">↗</span><span class="sr-only"> {{ __('ui.navigation.new_tab') }}</span></a></p>
    @endif

    @if(! $email && ! $profileLinks['linkedin'] && ! $profileLinks['github'])
        <p class="notice" role="status">{{ __('ui.contact.no_channels') }}</p>
    @endif

    @if($resumeAvailable)
        <p><a class="button button-primary" href="{{ route('resume.download') }}">{{ __('ui.actions.download_resume') }}</a></p>
    @endif
</section>
@endsection
