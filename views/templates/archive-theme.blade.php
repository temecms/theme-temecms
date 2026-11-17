{{--
    The theme directory: each theme as a card with its picture, name, line and facts, then the "build a theme" band.
    Themes are content of the "theme" type (fields: badge, meta, status; the featured image is the card's picture).
--}}
@extends('theme::layouts.base')

@section('main')
    <section class="page-hero" aria-labelledby="page-title">
        <div class="wrap">
            <h1 id="page-title">{{ $page->subject?->title }}</h1>
            <div class="lead region-lead"><x-teme::region name="themes-intro" /></div>
        </div>
    </section>

    <section class="sec tight" aria-label="{{ __('The directory') }}">
        <div class="wrap">
            <div class="grid-2">
                @foreach ($page->items as $item)
                    @php
                        $badge = (string) ($item->field('badge')?->text ?? '');
                        $facts = array_values(array_filter(array_map('trim', explode('·', (string) ($item->field('meta')?->text ?? '')))));
                    @endphp
                    <a class="tcard" href="{{ $item->url }}">
                        @if ($item->image !== null)
                            <div class="pv"><x-teme::image :id="$item->image->id" context="card" :alt="$item->image->alt" /></div>
                        @else
                            <div class="pv empty"><span>{{ __('Not built yet') }}</span></div>
                        @endif
                        <div class="m">
                            <b>{{ $item->title }} @if ($badge !== '')<span class="{{ $badge === 'Planned' ? 'tag' : 'first-party' }}">{{ $badge }}</span>@endif</b>
                            @if ($item->standfirst)<p>{{ $item->standfirst }}</p>@endif
                            <div class="meta">@foreach ($facts as $f)<span>{{ $f }}</span>@endforeach</div>
                        </div>
                    </a>
                @endforeach
            </div>
            @include('theme::partials.pagination', ['pagination' => $page->pagination])
        </div>
    </section>

    <section class="sec chrome" aria-label="{{ __('Build a theme') }}">
        <div class="wrap"><div class="sec-region"><x-teme::region name="themes-build" /></div></div>
    </section>
@endsection
