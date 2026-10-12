<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $description ?? config('portfolio.description') }}">
    <meta name="theme-color" content="#101413">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website"><meta property="og:title" content="{{ $title ?? 'Jackson Severino da Rocha — Backend Engineer' }}"><meta property="og:description" content="{{ $description ?? config('portfolio.description') }}"><meta property="og:url" content="{{ url()->current() }}">
    <title>{{ $title ?? 'Jackson Severino da Rocha — Backend Engineer' }}</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script type="application/ld+json">{"@@context":"https://schema.org","@@type":"Person","name":"Jackson Severino da Rocha","jobTitle":"Backend Engineer","knowsAbout":["Python","FastAPI","Java","Spring Boot","REST APIs","PostgreSQL","Docker"]}</script>
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header"><a class="wordmark" href="{{ route('home') }}" aria-label="Jackson Severino da Rocha home">JS<span>.</span></a><nav aria-label="Main navigation"><a href="{{ route('about') }}">About</a><a href="{{ route('projects.index') }}">Projects</a><a href="{{ route('open-source') }}">Open source</a><a href="{{ route('articles.index') }}">Writing</a><a href="{{ route('contact') }}">Contact</a></nav><button class="theme-toggle" type="button" aria-label="Toggle color theme">◐ <span>Theme</span></button></header>
<main id="main">@yield('content')</main>
<footer class="site-footer"><a class="wordmark" href="{{ route('home') }}">JS<span>.</span></a><p>Built with care. Content is published only when it can be supported by evidence.</p><ul aria-label="Professional profiles">@if($profileLinks['linkedin'])<li><a href="{{ $profileLinks['linkedin'] }}" target="_blank" rel="noopener noreferrer">LinkedIn<span class="sr-only"> (opens in a new tab)</span></a></li>@endif @if($profileLinks['github'])<li><a href="{{ $profileLinks['github'] }}" target="_blank" rel="noopener noreferrer">GitHub<span class="sr-only"> (opens in a new tab)</span></a></li>@endif</ul><a href="{{ route('sitemap') }}">Sitemap</a><small>© {{ date('Y') }} Jackson Severino da Rocha</small></footer>
</body></html>
