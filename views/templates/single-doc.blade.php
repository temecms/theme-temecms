{{--
    A guide, in the documentation's frame: the tree on the left (with the version switcher), the article in the middle
    (breadcrumbs, title, summary, body with anchors, code with its language and Copy, the callouts, tables that scroll),
    "On this page" on the right, then previous and next, Edit this page, when it was checked, and "Was this page helpful?".
--}}
@extends('theme::layouts.base')

@section('head-search')
    @include('theme::partials.dir.docs-head-search')
@endsection

@section('main')
    @include('theme::partials.directory-data')
    @php
        extract(temecms_docs_frame($navigation ?? null, $themeSettings ?? [], $teme ?? null, $site, (string) $page->url));
        $prose = temecms_doc_prose($page->bodyHtml);
        $toc = $prose['toc'];
        $group = trim((string) ($page->field('group')?->text ?? ''));
        $proof = trim((string) ($page->field('proof')?->text ?? ''));
        $proofKey = (string) ($page->field('proof')?->value ?? '');
        $editField = $page->field('edit_url');
        $editUrl = ($editField !== null && ! $editField->empty) ? (string) $editField->value : '';
        $at = collect($flat)->search(fn ($it) => (bool) ($it['current'] ?? false));
        $prev = $at !== false && $at > 0 ? $flat[$at - 1] : null;
        $next = $at !== false && $at < count($flat) - 1 ? $flat[$at + 1] : null;
        $when = $page->revised ?? $page->updated ?? $page->published;
        $checked = __('Updated :date', ['date' => $when->format('j M Y')]);
        if ($proof !== '' && $proofKey !== 'none') {
            $checked .= ' · '.$proof;
        }
        $index[] = ['title' => $page->title, 'url' => $page->url, 'summary' => (string) $page->standfirst, 'group' => $group !== '' ? $group : __('This page'), 'headings' => $toc];
    @endphp
    <div class="dx docs-page">
        <div class="docs-subbar">
            <button type="button" class="btn btn-quiet" data-tree-open aria-expanded="false" aria-controls="docs-tree" hidden>@include('theme::partials.dir.glyph', ['icon' => 'menu']){{ __('Menu') }}</button>
            <span class="where">@if ($group !== ''){{ $group }} › @endif{{ $page->title }}</span>
        </div>
        <div class="docs-shell">
            @include('theme::partials.dir.docs-tree')
            <div class="docs-main">
                @include('theme::partials.dir.docs-banner')
                <article class="article">
                    <nav class="d-crumbs" aria-label="{{ __('Breadcrumb') }}">@if ($docsHome !== '')<a href="{{ $docsHome }}">{{ __('Docs') }}</a><span aria-hidden="true">/</span>@endif @if ($group !== '')<span>{{ $group }}</span><span aria-hidden="true">/</span>@endif<span aria-current="page">{{ $page->title }}</span></nav>
                    <h1>{{ $page->title }}</h1>
                    @if ($page->standfirst !== null && $page->standfirst !== '')<p class="standfirst">{{ $page->standfirst }}</p>@endif
                    @include('theme::partials.dir.docs-otp-inline')
                    <div class="prose">{!! $prose['html'] !!}</div>
                    @include('theme::partials.dir.docs-foot', ['editUrl' => $editUrl, 'checkedLine' => $checked])
                </article>
            </div>
            @include('theme::partials.dir.docs-otp')
        </div>
        @include('theme::partials.dir.docs-search')
    </div>
@endsection
