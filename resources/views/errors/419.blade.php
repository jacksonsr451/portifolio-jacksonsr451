@extends('layouts.app')
@php($title = __('ui.errors.419_title').' — '.config('portfolio.name'))
@php($description = __('ui.errors.419_message'))
@section('robots', 'noindex, follow')
@section('content')
    @include('errors.status-content', ['statusCode' => '419', 'heading' => __('ui.errors.419_title'), 'message' => __('ui.errors.419_message')])
@endsection
