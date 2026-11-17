{{-- A post: back to the blog, its title, date and kind, its standfirst and words, the region after them and a sign-off with the icon. --}}
@extends('theme::layouts.base')

@section('main')
    <div class="wrap">
        <article class="post">
            <a class="back" href="{{ $site->url }}blog/">{{ __('Blog') }}</a>
            <h1>{{ $page->title }}</h1>
            <p class="meta-row"><time datetime="{{ $page->published->format('Y-m-d') }}">{{ $page->publishedText }}</time>@if ($page->kicker !== null)<span class="tag">{{ $page->kicker->name }}</span>@endif</p>
            @if ($page->standfirst !== null && $page->standfirst !== '')<p class="standfirst">{{ $page->standfirst }}</p>@endif
            <div class="prose">@include('theme::partials.prose-body', ['html' => $page->bodyHtml])</div>
            <x-teme::region name="after-content" />
            <div class="sign-off">@include('theme::partials.icon-mark')<span><b>{{ $site->name }}</b>@foreach ($page->authors as $author){{ $author->name }}@if (! $loop->last), @endif @endforeach</span></div>
        </article>
    </div>
@endsection
