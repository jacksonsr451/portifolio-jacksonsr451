@extends('layouts.app')
@php($title = __('ui.errors.404_title').' — '.config('portfolio.name'))
@php($description = __('ui.errors.404_message'))
@section('robots', 'noindex, follow')
@section('content')
    @include('errors.status-content', ['statusCode' => '404', 'heading' => __('ui.errors.404_title'), 'message' => __('ui.errors.404_message')])
@endsection
