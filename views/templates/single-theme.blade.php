{{-- A theme's own page: its name, facts, pictures, what it supports and how to get it. --}}
@extends('theme::layouts.base')

@section('main')
    @include('theme::partials.sectioned', ['page' => $page, 'variant' => 'landing'])
@endsection
