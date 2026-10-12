@extends('layouts.app')
@section('content')
<section class="section-wrap page-intro contact-page">
    <p class="eyebrow">Contact</p>
    <h1>Let’s make useful <em>things.</em></h1>
    <p class="lead">I’m interested in backend engineering opportunities and focused freelance work involving APIs and backend systems.</p>

    @if($email)
        <p><a class="button button-primary" href="mailto:{{ $email }}">Email {{ $email }}</a></p>
    @endif

    @if($profileLinks['linkedin'])
        <p><a class="button button-quiet" href="{{ $profileLinks['linkedin'] }}" target="_blank" rel="noopener noreferrer">Connect on LinkedIn <span aria-hidden="true">↗</span><span class="sr-only"> (opens in a new tab)</span></a></p>
    @endif

    @if($profileLinks['github'])
        <p><a class="button button-quiet" href="{{ $profileLinks['github'] }}" target="_blank" rel="noopener noreferrer">View GitHub profile <span aria-hidden="true">↗</span><span class="sr-only"> (opens in a new tab)</span></a></p>
    @endif

    @if(! $email && ! $profileLinks['linkedin'] && ! $profileLinks['github'])
        <p class="notice" role="status">No professional contact channel is configured yet.</p>
    @endif

    @if($resumeAvailable)
        <p><a class="button button-primary" href="{{ route('resume.download') }}">Download résumé (PDF)</a></p>
    @endif
</section>
@endsection
