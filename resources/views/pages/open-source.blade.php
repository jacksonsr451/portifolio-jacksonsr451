@extends('layouts.app')
@section('content')
<section class="section-wrap page-intro">
    <p class="eyebrow">Open source</p>
    <h1>Contribution, <em>with receipts.</em></h1>
    <p class="lead">A record of implementation and technical discussion, with status and public evidence for each contribution.</p>
</section>
<section class="section-wrap contribution-list" aria-label="Open-source contributions">
    @foreach($contributions as $item)
        <article class="contribution">
            <div>
                <p class="eyebrow">{{ $item['status'] }}</p>
                <h2>{{ $item['organization'] }} / {{ $item['repository'] }}</h2>
                <p>{{ $item['scope'] }}</p>
                <p>{{ $item['participation'] }}</p>
                <ul class="contribution-evidence" aria-label="Public evidence">
                    <li><a href="{{ $item['issue_url'] }}" target="_blank" rel="noopener noreferrer">Issue #{{ $item['issue'] }} <span class="sr-only">(opens in a new tab)</span></a></li>
                    @if(!empty($item['pull_request_url']) && filter_var($item['pull_request_url'], FILTER_VALIDATE_URL))
                        <li><a href="{{ $item['pull_request_url'] }}" target="_blank" rel="noopener noreferrer">PR #{{ $item['pull_request'] }} <span class="sr-only">(opens in a new tab)</span></a></li>
                    @endif
                    @if(!empty($item['related_url']) && filter_var($item['related_url'], FILTER_VALIDATE_URL))
                        <li><a href="{{ $item['related_url'] }}" target="_blank" rel="noopener noreferrer">{{ $item['related_label'] }} <span class="sr-only">(opens in a new tab)</span></a></li>
                    @endif
                </ul>
            </div>
        </article>
    @endforeach
</section>
@endsection
