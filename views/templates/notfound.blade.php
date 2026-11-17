{{-- Nothing at this address, said plainly, with a search and a way home. The status stays 404. --}}
@extends('theme::layouts.base')

@section('main')
    <section class="page-hero" aria-labelledby="page-title">
        <div class="wrap">
            <h1 id="page-title">{{ __('We couldn’t find that page') }}</h1>
            <p class="lead">{{ __('It may have moved, or the address may have a typo. Try a search, or start from the home page.') }}</p>
            <div class="cta-row" style="justify-content:flex-start">
                @include('theme::partials.search-form', ['id' => 'search-notfound', 'placeholder' => __('Search')])
                <a class="btn btn-quiet" href="{{ $site->url }}">{{ __('Go to the home page') }}</a>
            </div>
        </div>
    </section>
@endsection
