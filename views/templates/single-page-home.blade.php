{{-- The home page: the product's opening, four stops and the closing, drawn from the page's body; the blog's latest posts under them when the theme's setting is on. --}}
@extends('theme::layouts.base')

@section('main')
    @include('theme::partials.sectioned', ['page' => $page, 'variant' => 'home'])

    @if ($themeSettings['home_latest'] ?? false)
        <section class="sec chrome" aria-label="{{ __('From the blog') }}">
            <div class="wrap"><x-teme::region name="home-latest" /></div>
        </section>
    @endif
@endsection
