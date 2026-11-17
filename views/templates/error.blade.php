{{-- Any other error: the status and a way home. --}}
@extends('theme::layouts.base')

@section('main')
    <section class="page-hero" aria-labelledby="page-title">
        <div class="wrap">
            <h1 id="page-title">{{ __('Something went wrong') }}</h1>
            <p class="lead">{{ $page->code ?? '' }} {{ __('The page could not be shown. Try again in a moment.') }}</p>
            <div class="cta-row" style="justify-content:flex-start"><a class="btn btn-quiet" href="{{ $site->url }}">{{ __('Go to the home page') }}</a></div>
        </div>
    </section>
@endsection
