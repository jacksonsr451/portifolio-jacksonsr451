@extends('layouts.app')
@php($title = __('ui.errors.500_title').' — '.config('portfolio.name'))
@php($description = __('ui.errors.500_message'))
@section('robots', 'noindex, follow')
@section('content')
    @include('errors.status-content', ['statusCode' => '500', 'heading' => __('ui.errors.500_title'), 'message' => __('ui.errors.500_message')])
@endsection
