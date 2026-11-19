{{--
    The docs home, in the documentation's frame: a two-sentence welcome (the region Documentation: opening text), one
    card per audience with the page to start with, popular pages (the menu in the slot docs-popular, each with its
    guide's summary), and what's new (the region Documentation: what's new). The guides are the doc type's items.
--}}
@extends('theme::layouts.base')

@section('head-search')
    @include('theme::partials.dir.docs-head-search')
@endsection

@section('main')
    @include('theme::partials.directory-data')
    @php
        $here = (string) ($page->subject?->url ?? ($themeSettings['docs_address'] ?? '/'));
        extract(temecms_docs_frame($navigation ?? null, $themeSettings ?? [], $teme ?? null, $site, $here));
        $summaries = [];
        foreach ($page->items as $item) {
            $summaries[rtrim((string) $item->url, '/')] = (string) ($item->standfirst ?? '');
            $group = (string) ($item->field('group')?->text ?? '');
            $index[] = ['title' => $item->title, 'url' => $item->url, 'summary' => (string) ($item->standfirst ?? ''), 'group' => $group !== '' ? $group : (string) ($item->field('guide')?->text ?? '')];
        }
        $summaryOf = fn (?string $url) => $summaries[rtrim((string) $url, '/')] ?? '';
        $firstOf = function (string $slot) use ($navigation) {
            $f = temecms_docs_flat([['label' => '', 'current' => false, 'items' => $navigation[$slot] ?? []]]);

            return $f[0] ?? null;
        };
        $audiences = [
            ['Editors', 'Write, review and publish; media, pages and menus.', 'edit', $firstOf('docs-editors')],
            ['Operators', 'Install, configure, back up and update a site.', 'server', $firstOf('docs-operators')],
            ['Developers', 'Plugins, themes, the read API and hooks.', 'code', $firstOf('docs-developers')],
        ];
        $popular = temecms_docs_flat([['label' => '', 'current' => false, 'items' => $navigation['docs-popular'] ?? []]]);
        $toc = [['id' => 'start-here', 'text' => __('Start here')]];
        if ($popular !== []) {
            $toc[] = ['id' => 'popular', 'text' => __('Popular pages')];
        }
        // The region drawn here first, so an empty one leaves out its heading and its line in "On this page".
        $whatsNew = trim(\Illuminate\Support\Facades\Blade::render('<x-teme::region name="docs-whats-new" />'));
        if ($whatsNew !== '') {
            $toc[] = ['id' => 'whats-new', 'text' => __('What’s new')];
        }
        $prev = null;
        $next = $flat[0] ?? null;
        $title = $page->subject?->title ?? __('Documentation');
    @endphp
    <div class="dx docs-page">
        <div class="docs-subbar">
            <button type="button" class="btn btn-quiet" data-tree-open aria-expanded="false" aria-controls="docs-tree" hidden>@include('theme::partials.dir.glyph', ['icon' => 'menu']){{ __('Menu') }}</button>
            <span class="where">{{ $title }}</span>
        </div>
        <div class="docs-shell">
            @include('theme::partials.dir.docs-tree')
            <div class="docs-main">
                @include('theme::partials.dir.docs-banner')
                <article class="article">
                    <nav class="d-crumbs" aria-label="{{ __('Breadcrumb') }}"><span aria-current="page">{{ __('Docs') }}</span></nav>
                    <h1>{{ $title }}</h1>
                    <div class="standfirst region-standfirst"><x-teme::region name="docs-intro" /></div>
                    @include('theme::partials.dir.docs-otp-inline')

                    <h2 id="start-here"><a class="anchor" href="#start-here" aria-label="{{ __('Link to “:heading”', ['heading' => __('Start here')]) }}">#</a>{{ __('Start here') }}</h2>
                    <div class="aud-cards">
                        @foreach ($audiences as [$who, $line, $icon, $first])
                            @if ($first !== null)
                                <a class="lcard" href="{{ $first['url'] }}">@include('theme::partials.dir.glyph', ['icon' => $icon])<b>{{ __($who) }}</b><span>{{ __($line) }}</span><small>{{ __('Start with :page', ['page' => $first['label']]) }}</small></a>
                            @else
                                <div class="lcard">@include('theme::partials.dir.glyph', ['icon' => $icon])<b>{{ __($who) }}</b><span>{{ __($line) }}</span></div>
                            @endif
                        @endforeach
                    </div>

                    @if ($popular !== [])
                        <h2 id="popular"><a class="anchor" href="#popular" aria-label="{{ __('Link to “:heading”', ['heading' => __('Popular pages')]) }}">#</a>{{ __('Popular pages') }}</h2>
                        <div class="link-cards">
                            @foreach ($popular as $it)
                                <a class="lcard" href="{{ $it['url'] }}"><b>{{ $it['label'] }}</b>@if ($summaryOf($it['url']) !== '')<span>{{ $summaryOf($it['url']) }}</span>@endif</a>
                            @endforeach
                        </div>
                    @endif

                    @if ($whatsNew !== '')
                    <h2 id="whats-new"><a class="anchor" href="#whats-new" aria-label="{{ __('Link to “:heading”', ['heading' => __('What’s new')]) }}">#</a>{{ __('What’s new') }}@if ($versionLabel !== '' || $versions !== []) {{ __('in :version', ['version' => $versions !== [] ? $versions[0]['label'] : $versionLabel]) }}@endif</h2>
                    <div class="whatsnew-region">{!! $whatsNew !!}</div>
                    @endif

                    @include('theme::partials.dir.docs-foot', ['editUrl' => '', 'checkedLine' => '', 'prev' => null, 'next' => null])
                </article>
            </div>
            @include('theme::partials.dir.docs-otp')
        </div>
        @include('theme::partials.dir.docs-search')
    </div>
@endsection
