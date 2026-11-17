{{--
    A guide, set in full: the guide's tree (a menu in the slot docs-{guide}), the article with its standfirst, proof mark, steps,
    code and notes, the page's own contents ("On this page", made from its headings), and the previous and next guide taken from the tree.
--}}
@extends('theme::layouts.base')

@section('main')
    @php
        $guide = (string) ($page->field('guide')?->value ?? '');
        $guideLabel = (string) ($page->field('guide')?->text ?? '');
        $group = (string) ($page->field('group')?->text ?? '');
        $proof = (string) ($page->field('proof')?->text ?? '');
        $proofKey = (string) ($page->field('proof')?->value ?? '');
        $writtenFor = (string) ($page->field('written_for')?->text ?? '');
        $isSoon = (bool) ($page->field('soon')?->value ?? false);
        $slot = 'docs-'.$guide;

        // The body's headings get anchors, and "On this page" lists the h2s.
        libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<?xml encoding="utf-8" ?><body>'.$page->bodyHtml.'</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        $toc = [];
        foreach ($dom->getElementsByTagName('pre') as $pre) {
            $pre->setAttribute('tabindex', '0');
        }
        foreach (['h2', 'h3'] as $level) {
            foreach ($dom->getElementsByTagName($level) as $h) {
                if (! $h->hasAttribute('id')) {
                    $h->setAttribute('id', trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($h->textContent)), '-'));
                }
            }
        }
        foreach ($dom->getElementsByTagName('h2') as $h) {
            $toc[] = ['id' => $h->getAttribute('id'), 'text' => $h->textContent];
        }
        $bodyHtml = '';
        foreach ($dom->getElementsByTagName('body')->item(0)->childNodes as $n) {
            $bodyHtml .= $dom->saveHTML($n);
        }

        // Previous and next: the tree's items in order, around the current one.
        $flat = [];
        $walk = function (array $items) use (&$walk, &$flat) {
            foreach ($items as $it) {
                if (! $it['heading'] && $it['url'] !== null) {
                    $flat[] = $it;
                }
                $walk($it['children'] ?? []);
            }
        };
        $walk($navigation[$slot] ?? []);
        $at = collect($flat)->search(fn ($it) => $it['current']);
        $prev = $at !== false && $at > 0 ? $flat[$at - 1] : null;
        $next = $at !== false && $at < count($flat) - 1 ? $flat[$at + 1] : null;
    @endphp

    <div class="docs-bar">
        <div class="wrap">
            <nav class="crumbs" aria-label="{{ __('Breadcrumb') }}"><a href="{{ $site->url }}docs/">{{ __('Docs') }}</a><span aria-hidden="true">/</span><span>{{ $guideLabel }}</span><span aria-hidden="true">/</span><b>{{ $group }}</b></nav>
            @include('theme::partials.search-form', ['id' => 'docs-q', 'label' => __('Search the documentation'), 'placeholder' => __('Search the docs')])
            <div class="end"><span class="ver"><span>{{ __('Version') }}</span> <b>{{ $themeSettings['docs_version'] ?? '' }}</b></span></div>
        </div>
    </div>

    <div class="wrap docs-shell">
        <div class="docs-tree">
            <details class="tree-details" open>
                <summary>{{ $guideLabel }}</summary>
                <x-teme::navigation :slot="$slot" :label="$guideLabel" />
            </details>
        </div>

        <div class="docs-main">
            <article class="article">
                <p class="kick">{{ $guideLabel }} · {{ $group }}</p>
                <h1>{{ $page->title }}</h1>
                @if ($page->standfirst !== null && $page->standfirst !== '')<p class="standfirst">{{ $page->standfirst }}</p>@endif
                <p class="meta-row">
                    @if ($proof !== '')<span class="tag {{ $proofKey === 'none' ? '' : 'ok' }}">{{ $proof }}</span>@endif
                    @if ($writtenFor !== '')<span>{{ __('Written for :what', ['what' => $writtenFor]) }}</span>@endif
                    @if ($isSoon)<span class="tag">{{ __('Soon') }}</span>@endif
                </p>
                @if ($toc !== [])
                    <details class="otp-inline"><summary>{{ __('On this page') }}</summary><ul>@foreach ($toc as $t)<li><a href="#{{ $t['id'] }}">{{ $t['text'] }}</a></li>@endforeach</ul></details>
                @endif

                <div class="prose">{!! $bodyHtml !!}</div>

                @if ($prev !== null || $next !== null)
                    <nav class="pager" aria-label="{{ __('Previous and next') }}">
                        @if ($prev !== null)<a class="prev" href="{{ $prev['url'] }}"><small>{{ __('Previous') }}</small><b>{{ $prev['label'] }}</b></a>@endif
                        @if ($next !== null)<a class="next" href="{{ $next['url'] }}"><small>{{ __('Next') }}</small><b>{{ $next['label'] }}</b></a>@endif
                    </nav>
                @endif
            </article>
        </div>

        @if ($toc !== [])
            <aside class="otp" aria-labelledby="otp-h">
                <p class="otp-h" id="otp-h">{{ __('On this page') }}</p>
                <ul>@foreach ($toc as $t)<li><a href="#{{ $t['id'] }}">{{ $t['text'] }}</a></li>@endforeach</ul>
                <a class="back-top small" href="#top">{{ __('Back to the top') }}</a>
            </aside>
        @endif
    </div>
@endsection
