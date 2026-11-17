{{-- The blog: its heading and opening words, then every post as a full-width row, newest first. --}}
@extends('theme::layouts.base')

@section('main')
    <section class="page-hero" aria-labelledby="page-title">
        <div class="wrap">
            <span class="kicker">{{ __('Blog') }}</span>
            <h1 id="page-title">{{ $page->feed->subject?->title ?? $site->name }}</h1>
            <div class="lead region-lead"><x-teme::region name="blog-intro" /></div>
        </div>
    </section>
    <section class="sec tight" aria-label="{{ __('Posts') }}">
        <div class="wrap">
            @if ($page->lead === null && $page->featured === [] && $page->feed->items === [])
                <p class="empty-note">{{ __('Nothing published yet.') }}</p>
            @else
                <div class="post-list">
                    @if ($page->lead !== null)@include('theme::partials.post-row', ['item' => $page->lead])@endif
                    @foreach ($page->featured as $item)@include('theme::partials.post-row', ['item' => $item])@endforeach
                    @foreach ($page->feed->items as $item)@include('theme::partials.post-row', ['item' => $item])@endforeach
                </div>
                @include('theme::partials.pagination', ['pagination' => $page->feed->pagination])
            @endif
        </div>
    </section>
@endsection
