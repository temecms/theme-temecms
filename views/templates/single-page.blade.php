{{-- A page with no template of its own: its title, its words and the region after them. --}}
@extends('theme::layouts.base')

@section('main')
    <section class="page-hero" aria-labelledby="page-title">
        <div class="wrap">
            @if ($page->breadcrumbs !== null && count($page->breadcrumbs) > 1)
                @include('theme::partials.crumbs', ['crumbs' => $page->breadcrumbs])
            @endif
            <h1 id="page-title">{{ $page->title }}</h1>
            @if ($page->standfirst !== null && $page->standfirst !== '')<p class="lead">{{ $page->standfirst }}</p>@endif
        </div>
    </section>
    <section class="sec tight">
        <div class="wrap">
            <div class="prose">@include('theme::partials.prose-body', ['html' => $page->bodyHtml])</div>
            <x-teme::region name="after-content" />
        </div>
    </section>
@endsection
