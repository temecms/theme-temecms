{{--
    A plugin's page: its header with one primary button (Install opens the steps under it) and Source, then five tabs
    (Overview, Screenshots, Changelog, Permissions, Support) beside a column of labelled facts. Read from the temecms-site
    plugin's fields on the plugin type.
--}}
@extends('theme::layouts.base')

@section('main')
    @include('theme::partials.directory-data')
    @php
        $listing = temecms_listing($page);
        $dirUrl = (string) ($themeSettings['plugins_address'] ?? '');
        $catBase = $dirUrl;
        $tabs = [
            ['id' => 'overview', 'label' => __('Overview'), 'count' => null, 'view' => 'theme::partials.dir.overview', 'data' => ['html' => $page->bodyHtml, 'listing' => $listing]],
            ['id' => 'shots', 'label' => __('Screenshots'), 'count' => count($listing['screenshots']) ?: null, 'view' => 'theme::partials.dir.shots', 'data' => ['listing' => $listing]],
            ['id' => 'changelog', 'label' => __('Changelog'), 'count' => null, 'view' => 'theme::partials.dir.changelog', 'data' => ['listing' => $listing]],
            ['id' => 'perms', 'label' => __('Permissions'), 'count' => count($listing['capabilities']) ?: null, 'view' => 'theme::partials.dir.perms', 'data' => ['listing' => $listing]],
            ['id' => 'support', 'label' => __('Support'), 'count' => null, 'view' => 'theme::partials.dir.support', 'data' => ['listing' => $listing]],
        ];
    @endphp
    <div class="dx"><div class="wrap item-page">
        @include('theme::partials.dir.crumbs', ['dirName' => __('Plugins'), 'dirUrl' => $dirUrl])
        <header class="item-head">
            @include('theme::partials.dir.icon', ['listing' => $listing, 'size' => 'lg'])
            <div>
                <h1>{{ $listing['title'] }}</h1>
                @if ($listing['summary'] !== '')<p class="sum">{{ $listing['summary'] }}</p>@endif
                <p class="byline">
                    @if ($listing['author'] !== '')<span>{{ __('By') }} @if ($listing['authorUrl'] !== '')<a href="{{ $listing['authorUrl'] }}">{{ $listing['author'] }}</a>@else{{ $listing['author'] }}@endif</span>@endif
                    @if ($listing['version'] !== '')<span class="num">{{ __('Version :v', ['v' => $listing['version']]) }}</span>@endif
                    @if ($listing['updated'] !== null)<span>{{ __('Updated') }} @include('theme::partials.dir.date', ['date' => $listing['updated'], 'relative' => true])</span>@endif
                    @if ($listing['works'] !== [])<span class="works">@include('theme::partials.dir.glyph', ['icon' => 'check']){{ __('Works with :what', ['what' => temecms_works_short($listing)]) }}</span>@endif
                </p>
            </div>
            <div class="item-actions">
                <a class="btn btn-primary" href="#install-panel" data-install aria-controls="install-panel">{{ __('Install') }}</a>
                @if ($listing['links']['source'] !== '')<a class="btn btn-quiet" href="{{ $listing['links']['source'] }}">@include('theme::partials.dir.glyph', ['icon' => 'github']){{ __('Source') }}</a>@endif
            </div>
        </header>
        @include('theme::partials.dir.install', ['isTheme' => false])
        <div class="item-layout">
            <div class="item-main">@include('theme::partials.dir.tabs', ['label' => __('About :name', ['name' => $listing['title']])])</div>
            @include('theme::partials.dir.facts', ['isTheme' => false])
        </div>
    </div></div>
@endsection
