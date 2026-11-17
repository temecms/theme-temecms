{{-- The search page, at /?s=: the form prefilled, how many were found, and each result with its snippet. --}}
@extends('theme::layouts.base')

@section('main')
    <section class="page-hero" aria-labelledby="page-title">
        <div class="wrap">
            <h1 id="page-title">{{ $page->subject?->title }}</h1>
            @include('theme::partials.search-form', ['id' => 'search-results', 'value' => $page->search?->query ?? '', 'placeholder' => __('Search')])
        </div>
    </section>
    <section class="sec tight" aria-label="{{ __('Results') }}">
        <div class="wrap">
            @if ($page->search === null || $page->search->empty)
                <p class="empty-note">{{ __('Type a word or two to search the site.') }}</p>
            @elseif ($page->search->results === [])
                <p class="empty-note" role="status">{{ __('Nothing matched “:query”. Try fewer words, or check the spelling.', ['query' => $page->search->query]) }}</p>
            @else
                <p class="muted small" role="status">{{ $page->search->total === 1 ? __('1 result') : __(':count results', ['count' => $page->search->total]) }}</p>
                <div class="post-list">
                    @foreach ($page->search->results as $result)
                        <article>
                            <time datetime="{{ $result->item->published->format('Y-m-d') }}">{{ $result->item->publishedText }}</time>
                            <div><h2><a href="{{ $result->item->url }}">{{ $result->item->title }}</a></h2><p>{!! $result->snippetHtml !!}</p></div>
                        </article>
                    @endforeach
                </div>
                @include('theme::partials.pagination', ['pagination' => $page->pagination])
            @endif
        </div>
    </section>
@endsection
