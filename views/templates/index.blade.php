{{-- The floor every theme has: any kind of page may land here, with any view model, so it reads nothing from $page. --}}
@extends('theme::layouts.base')

@section('main')
    <section class="page-hero">
        <div class="wrap">
            <h1>{{ $site->name }}</h1>
            <p class="lead">{{ $site->tagline }}</p>
        </div>
    </section>
@endsection
