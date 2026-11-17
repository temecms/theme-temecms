{{--
    The plugin directory: a search, the categories, and the plugins in rows grouped by purpose, each with what it asks for.
    Plugins are content of the "plugin" type; this draws them from their fields (category, icon, asks, where_short, status).
--}}
@extends('theme::layouts.base')

@section('main')
    @php
        $rows = collect($page->items)->map(fn ($item) => [
            'item' => $item,
            'cat' => (string) ($item->field('category')?->value ?? ''),
            'catLabel' => (string) ($item->field('category')?->text ?? ''),
            'icon' => (string) ($item->field('icon')?->value ?? 'layers'),
            'asks' => (string) ($item->field('asks')?->text ?? ''),
            'where' => (string) ($item->field('where_short')?->text ?? ''),
            'status' => (string) ($item->field('status')?->text ?? ''),
        ]);
        $cats = $rows->pluck('catLabel', 'cat')->all();
    @endphp
    <section class="page-hero" aria-labelledby="page-title">
        <div class="wrap">
            <h1 id="page-title">{{ $page->subject?->title }}</h1>
            <div class="lead region-lead"><x-teme::region name="plugins-intro" /></div>
        </div>
    </section>

    <section class="sec tight" aria-label="{{ __('The directory') }}" data-filter>
        <div class="wrap">
            <div class="dir-top">
                @include('theme::partials.search-form', ['id' => 'pq', 'class' => '', 'label' => __('Search plugins'), 'placeholder' => __('Search plugins  ( / )'), 'filter' => true])
                <div class="chips" role="group" aria-label="{{ __('Category') }}" data-chips hidden>
                    <button type="button" data-filter-cat="all" aria-pressed="true">{{ __('All') }}</button>
                    @foreach ($cats as $key => $label)<button type="button" data-filter-cat="{{ $key }}" aria-pressed="false">{{ $label }}</button>@endforeach
                </div>
            </div>
            <p class="muted small" style="margin-top:14px"><span data-filter-count>{{ trans_choice(':count shown|:count shown', $rows->count(), ['count' => $rows->count()]) }}</span></p>
            @foreach ($cats as $key => $label)
                <section class="dir-group" data-group aria-labelledby="g-{{ $key }}">
                    <h2 id="g-{{ $key }}">{{ $label }}</h2>
                    <div class="dir-list">
                        @foreach ($rows->where('cat', $key) as $row)
                            <a class="prow" href="{{ $row['item']->url }}" data-item data-cat="{{ $key }}">
                                @include('theme::partials.plug-icon', ['icon' => $row['icon']])
                                <span><b>{{ $row['item']->title }}</b><span class="line">{{ $row['item']->standfirst }}</span></span>
                                <span class="where">{{ __('Asks for') }}: {{ $row['asks'] }}<em>{{ $row['where'] }}</em></span>
                                <span class="tags">@if ($row['status'] !== '')<span class="tag">{{ $row['status'] }}</span>@endif</span>
                                <span class="chev" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="m9 6 6 6-6 6"/></svg></span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach
            <p class="empty-note" data-filter-empty hidden>{{ __('No plugin matches. Try another word.') }}</p>
            @include('theme::partials.pagination', ['pagination' => $page->pagination])
        </div>
    </section>

    <section class="section chrome" aria-label="{{ __('How plugins work') }}">
        <div class="wrap"><div class="sec-region"><x-teme::region name="plugins-how" /></div></div>
    </section>
@endsection
