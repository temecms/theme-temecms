{{--
    The documentation's home: who the guides are for, then each guide's groups, with their proof marks and "Soon" tags.
    The guides are content of the "doc" type (a docs-sync plugin will fill them); this only draws them, grouped by their
    fields: guide, group, order, proof and soon.
--}}
@extends('theme::layouts.base')

@section('main')
    @php
        $rows = collect($page->items)->map(fn ($item) => [
            'item' => $item,
            'guide' => (string) ($item->field('guide')?->value ?? ''),
            'guideLabel' => (string) ($item->field('guide')?->text ?? ''),
            'group' => (string) ($item->field('group')?->text ?? ''),
            'order' => (int) ($item->field('order')?->value ?? 0),
            'soon' => (bool) ($item->field('soon')?->value ?? false),
            'proof' => (string) ($item->field('proof')?->text ?? ''),
        ])->sortBy('order')->values();
        $guides = ['editors', 'operators', 'developers'];
        $written = $rows->where('soon', false)->count();
        $soon = $rows->where('soon', true)->count();
        $who = [
            'editors' => ['Editors', 'You write, review and publish.', '<path d="M4 20h4L19 9l-4-4L4 16Z"/><path d="m13 7 4 4"/>'],
            'operators' => ['Operators', 'You run the server.', '<rect x="3" y="4" width="18" height="7" rx="2"/><rect x="3" y="13" width="18" height="7" rx="2"/><path d="M7 7.5h.01M7 16.5h.01"/>'],
            'developers' => ['Developers', 'You build themes and plugins.', '<path d="m8 8-5 4 5 4M16 8l5 4-5 4M14 4l-4 16"/>'],
        ];
    @endphp
    <div data-filter>
        <div class="docs-bar">
            <div class="wrap">
                <nav class="crumbs" aria-label="{{ __('Breadcrumb') }}"><b>{{ $page->subject?->title }}</b></nav>
                @include('theme::partials.search-form', ['id' => 'docs-q', 'label' => __('Search the documentation'), 'placeholder' => __('Search the docs  ( / )'), 'filter' => true])
                @include('theme::partials.docs-version')
            </div>
        </div>

        <div class="wrap">
            <section class="page-hero" aria-labelledby="page-title" style="padding-bottom:0">
                <h1 id="page-title">{{ $page->subject?->title }}</h1>
                <div class="lead region-lead"><x-teme::region name="docs-intro" /></div>
            </section>

            <section aria-labelledby="who-h" style="padding-top:40px">
                <h2 id="who-h" class="vh">{{ __('Who are you?') }}</h2>
                <div class="who-docs">
                    @foreach ($guides as $g)
                        <a href="#{{ $g }}"><span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">{!! $who[$g][2] !!}</svg></span><span><b>{{ __($who[$g][0]) }}</b><span>{{ __($who[$g][1]) }}</span></span></a>
                    @endforeach
                </div>
            </section>

            @foreach ($guides as $g)
                @php $inGuide = $rows->where('guide', $g)->values(); @endphp
                @if ($inGuide->isNotEmpty())
                    <section class="doc-sec" id="{{ $g }}" data-group aria-labelledby="{{ $g }}-h">
                        <h2 id="{{ $g }}-h">{{ $inGuide->first()['guideLabel'] }}</h2>
                        <div class="doc-groups">
                            @foreach ($inGuide->groupBy('group') as $group => $items)
                                <div class="doc-group" data-group>
                                    <h3>{{ $group }}</h3>
                                    <ul>
                                        @foreach ($items as $row)
                                            @if ($row['soon'])
                                                <li data-item><span class="x"><span class="t">{{ $row['item']->title }}</span><span class="tag">{{ __('Soon') }}</span></span></li>
                                            @else
                                                <li data-item><a href="{{ $row['item']->url }}"><span class="t">{{ $row['item']->title }}</span>@if ($row['proof'] !== '')<span class="proof">{{ $row['proof'] }}</span>@endif</a></li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endforeach

            <p class="empty-note" data-filter-empty hidden>{{ __('Nothing in the documentation matches. Try another word.') }}</p>
            <p class="muted small" style="margin:48px 0 72px">{{ __('Written: :written pages. Soon: :soon.', ['written' => $written, 'soon' => $soon]) }}</p>
            @include('theme::partials.pagination', ['pagination' => $page->pagination])
        </div>
    </div>
@endsection
