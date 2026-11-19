{{--
    A theme's page: its header with Preview before Install, a gallery (a desktop frame with a phone frame over it and a
    row of its pictures), the Overview and Changelog tabs, and the facts column. Read from the temecms-site plugin's fields
    on the theme type; a picture whose caption names a phone goes in the phone frame.
--}}
@extends('theme::layouts.base')

@section('main')
    @include('theme::partials.directory-data')
    @php
        $listing = temecms_listing($page);
        $dirUrl = (string) ($themeSettings['themes_address'] ?? '');
        $catBase = '';
        $shots = $listing['screenshots'];
        if ($shots === [] && $listing['image'] !== null) {
            $shots = [['media' => $listing['image']->id, 'caption' => '']];
        }
        $isPhone = fn (array $s) => (bool) preg_match('/\b(phone|mobile)\b/i', (string) ($s['caption'] ?? ''));
        $phone = collect($shots)->first($isPhone);
        $wide = array_values(array_filter($shots, fn ($s) => ! $isPhone($s)));
        if ($wide === [] && $phone !== null) {
            $wide = [$phone];
        }
        $tabs = [
            ['id' => 'overview', 'label' => __('Overview'), 'count' => null, 'view' => 'theme::partials.dir.overview', 'data' => ['html' => $page->bodyHtml, 'listing' => $listing]],
            ['id' => 'changelog', 'label' => __('Changelog'), 'count' => null, 'view' => 'theme::partials.dir.changelog', 'data' => ['listing' => $listing]],
        ];
    @endphp
    <div class="dx"><div class="wrap item-page">
        @include('theme::partials.dir.crumbs', ['dirName' => __('Themes'), 'dirUrl' => $dirUrl])
        <header class="item-head no-icon">
            <div>
                <h1>{{ $listing['title'] }}</h1>
                @if ($listing['summary'] !== '')<p class="sum">{{ $listing['summary'] }}</p>@endif
                <p class="byline">
                    @if ($listing['author'] !== '')<span>{{ __('By') }} @if ($listing['authorUrl'] !== '')<a href="{{ $listing['authorUrl'] }}">{{ $listing['author'] }}</a>@else{{ $listing['author'] }}@endif</span>@endif
                    @if ($listing['version'] !== '')<span class="num">{{ __('Version :v', ['v' => $listing['version']]) }}</span>@endif
                    @if ($listing['updated'] !== null)<span>{{ __('Updated') }} @include('theme::partials.dir.date', ['date' => $listing['updated']])</span>@endif
                    @if ($listing['works'] !== [])<span class="works">@include('theme::partials.dir.glyph', ['icon' => 'check']){{ __('Works with :what', ['what' => temecms_works_short($listing)]) }}</span>@endif
                </p>
            </div>
            <div class="item-actions">
                @if ($wide !== [])<a class="btn btn-quiet" href="#gallery" data-preview>@include('theme::partials.dir.glyph', ['icon' => 'eye']){{ __('Preview') }}</a>@endif
                <a class="btn btn-primary" href="#install-panel" data-install aria-controls="install-panel">{{ __('Install') }}</a>
            </div>
        </header>
        @include('theme::partials.dir.install', ['isTheme' => true])
        <div class="item-layout">
            <div class="item-main">
                @if ($wide !== [])
                    <figure class="gallery theme-gallery" id="gallery" data-theme-gallery>
                        <div class="stage-wrap @if ($phone === null) no-phone @endif">
                            <div class="frame-d"><div class="shot-bar" aria-hidden="true"><i></i><i></i><i></i></div>
                                @foreach ($wide as $i => $shot)
                                    <div class="frame-img" data-view="{{ $i }}" @if ($i > 0) hidden @endif><x-teme::image :id="$shot['media']" context="shot" :alt="(string) ($shot['caption'] ?? '') !== '' ? $shot['caption'] : __(':name, on a wide screen', ['name' => $listing['title']])" :priority="$i === 0" /></div>
                                @endforeach
                            </div>
                            @if ($phone !== null)
                                <div class="frame-p"><div class="screen"><x-teme::image :id="$phone['media']" context="thumb" :alt="(string) $phone['caption']" /></div></div>
                            @endif
                        </div>
                        <figcaption class="muted small" data-view-cap>{{ $wide[0]['caption'] ?? '' }}</figcaption>
                        @if (count($wide) > 1)
                            <div class="thumbs" role="group" aria-label="{{ __('Templates') }}" data-view-thumbs>
                                @foreach ($wide as $i => $shot)
                                    <button type="button" data-view-btn="{{ $i }}" data-cap="{{ $shot['caption'] ?? '' }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}"><span class="mini"><x-teme::image :id="$shot['media']" context="thumb" :decorative="true" /></span><span>{{ ($shot['caption'] ?? '') !== '' ? $shot['caption'] : __('Picture :n', ['n' => $i + 1]) }}</span></button>
                                @endforeach
                            </div>
                        @endif
                    </figure>

                    <dialog class="pv" aria-label="{{ __('Preview of :name', ['name' => $listing['title']]) }}" data-pv>
                        <div class="pv-bar">
                            <b>{{ __('Preview: :name', ['name' => $listing['title']]) }}</b>
                            @if ($phone !== null)
                                <div class="switch" role="group" aria-label="{{ __('Screen') }}"><button type="button" data-pv-screen="desktop" aria-pressed="true">{{ __('Desktop') }}</button><button type="button" data-pv-screen="phone" aria-pressed="false">{{ __('Phone') }}</button></div>
                            @endif
                            @if (count($wide) > 1)
                                <label class="vh" for="pv-view">{{ __('Template') }}</label>
                                <select class="select pv-sel" id="pv-view" data-pv-view>@foreach ($wide as $i => $shot)<option value="{{ $i }}">{{ ($shot['caption'] ?? '') !== '' ? $shot['caption'] : __('Picture :n', ['n' => $i + 1]) }}</option>@endforeach</select>
                            @endif
                            <button type="button" class="btn btn-quiet btn-sm" data-pv-close>{{ __('Close') }}</button>
                        </div>
                        <div class="pv-stage">
                            <div class="pv-frame" data-pv-frame>
                                @foreach ($wide as $i => $shot)<div class="pv-img" data-pv-img="{{ $i }}" @if ($i > 0) hidden @endif><x-teme::image :id="$shot['media']" context="wide" :decorative="true" /></div>@endforeach
                                @if ($phone !== null)<div class="pv-img" data-pv-img="phone" hidden><x-teme::image :id="$phone['media']" context="thumb" :decorative="true" /></div>@endif
                            </div>
                        </div>
                    </dialog>
                @endif
                <div class="theme-tabs">@include('theme::partials.dir.tabs', ['label' => __('About :name', ['name' => $listing['title']])])</div>
            </div>
            @include('theme::partials.dir.facts', ['isTheme' => true])
        </div>
    </div></div>
@endsection
