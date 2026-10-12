@extends('layouts.app')
@php($title = __('ui.errors.403_title').' — '.config('portfolio.name'))
@php($description = __('ui.errors.403_message'))
@section('robots', 'noindex, follow')
@section('content')
    @include('errors.status-content', ['statusCode' => '403', 'heading' => __('ui.errors.403_title'), 'message' => __('ui.errors.403_message')])
@endsection
