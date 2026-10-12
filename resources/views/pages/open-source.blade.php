@extends('layouts.app')
@section('content')
<section class="section-wrap page-intro">
    <p class="eyebrow">{{ __('ui.open_source.eyebrow') }}</p>
    <h1>{{ __('ui.open_source.title') }} <em>{{ __('ui.open_source.title_em') }}</em></h1>
    <p class="lead">{{ __('ui.open_source.intro') }}</p>
</section>
<section class="section-wrap contribution-list" aria-label="{{ __('ui.open_source.list_label') }}">
    @foreach($contributions as $item)
        <article class="contribution">
            <div>
                <p class="eyebrow">{{ $item['status'] }}</p>
                <h2>{{ $item['organization'] }} / {{ $item['repository'] }}</h2>
                <p>{{ $item['scope'] }}</p>
                <p>{{ $item['participation'] }}</p>
                <ul class="contribution-evidence" aria-label="{{ __('ui.open_source.evidence_label') }}">
                    <li><a href="{{ $item['issue_url'] }}" target="_blank" rel="noopener noreferrer">{{ __('ui.actions.issue', ['number' => $item['issue']]) }} <span class="sr-only">{{ __('ui.navigation.new_tab') }}</span></a></li>
                    @if(!empty($item['pull_request_url']) && filter_var($item['pull_request_url'], FILTER_VALIDATE_URL))
                        <li><a href="{{ $item['pull_request_url'] }}" target="_blank" rel="noopener noreferrer">{{ __('ui.actions.pull_request', ['number' => $item['pull_request']]) }} <span class="sr-only">{{ __('ui.navigation.new_tab') }}</span></a></li>
                    @endif
                    @if(!empty($item['related_url']) && filter_var($item['related_url'], FILTER_VALIDATE_URL))
                        <li><a href="{{ $item['related_url'] }}" target="_blank" rel="noopener noreferrer">{{ __('ui.open_source.related_issue') }} <span class="sr-only">{{ __('ui.navigation.new_tab') }}</span></a></li>
                    @endif
                </ul>
            </div>
        </article>
    @endforeach
</section>
@endsection
