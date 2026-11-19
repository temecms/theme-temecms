{{-- The header: the product's icon and the site's name, the primary menu, the follow link, and, on small screens, a menu that opens without JavaScript: the primary menu, the theme's few more links (FAQ and Roadmap by default), and the follow link as a full-width button. Its icon turns into × while it is open. --}}
@php
    $moreLinks = [];
    foreach (array_filter(array_map('trim', explode(',', (string) ($themeSettings['menu_more'] ?? '')))) as $pair) {
        [$moreLabel, $moreUrl] = array_pad(array_map('trim', explode('=', $pair, 2)), 2, '');
        if ($moreLabel !== '' && $moreUrl !== '') {
            $moreLinks[] = ['label' => $moreLabel, 'url' => $moreUrl, 'current' => rtrim((string) parse_url($moreUrl, PHP_URL_PATH), '/') === rtrim((string) parse_url(request()->getRequestUri(), PHP_URL_PATH), '/')];
        }
    }
@endphp
<header class="site-head" id="top">
    <div class="wrap head-row">
        <a class="brand" href="{{ $site->url }}" aria-label="{{ $site->name }}, {{ __('home') }}">@include('theme::partials.icon-mark'){{ $site->name }}</a>
        <div class="nav"><x-teme::navigation slot="primary" :label="__('Main')" /></div>
        <div class="head-end">
            @yield('head-search')
            @include('theme::partials.follow', ['class' => 'btn btn-quiet follow-wide'])
            <details class="menu">
                <summary class="menu-btn"><svg class="menu-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4 7h16M4 12h16M4 17h16"/></svg><svg class="menu-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6 6 18"/></svg><span class="vh">{{ __('Menu') }}</span></summary>
                <div class="mobile-nav">
                    <x-teme::navigation slot="primary" :label="__('Main, small screens')" />
                    @if ($moreLinks !== [])
                        <ul class="menu-more">
                            @foreach ($moreLinks as $link)<li><a href="{{ $link['url'] }}"@if ($link['current']) aria-current="page"@endif>{{ $link['label'] }}</a></li>@endforeach
                        </ul>
                    @endif
                    @include('theme::partials.follow', ['class' => 'btn btn-primary', 'icon' => true])
                </div>
            </details>
        </div>
    </div>
</header>
