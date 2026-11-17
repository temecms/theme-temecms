{{-- The "roadmap" page template: an opening and sections drawn from the page's body (partials/sectioned). --}}
@extends('theme::layouts.base')

@section('main')
    @include('theme::partials.sectioned', ['page' => $page, 'variant' => 'roadmap'])

    <x-teme::region name="after-content" />
@endsection
