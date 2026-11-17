{{-- A category, tag, author or date: its title, its description, and its posts as rows. --}}
@extends('theme::layouts.base')

@section('main')
    <section class="page-hero" aria-labelledby="page-title">
        <div class="wrap">
            <span class="kicker">{{ __('Blog') }}</span>
            <h1 id="page-title">{{ $page->subject?->title }}</h1>
            @if ($page->subject?->description)<p class="lead">{{ $page->subject->description }}</p>@endif
        </div>
    </section>
    <section class="sec tight" aria-label="{{ __('Posts') }}">
        <div class="wrap">
            @if ($page->items === [])
                <p class="empty-note">{{ __('Nothing published here yet.') }}</p>
            @else
                <div class="post-list">@foreach ($page->items as $item)@include('theme::partials.post-row', ['item' => $item])@endforeach</div>
                @include('theme::partials.pagination', ['pagination' => $page->pagination])
            @endif
        </div>
    </section>
@endsection
