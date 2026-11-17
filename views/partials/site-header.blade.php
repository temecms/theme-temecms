{{-- The header: the product's icon and the site's name, the primary menu, the follow link, and, on small screens, a menu that opens without JavaScript. --}}
<header class="site-head" id="top">
    <div class="wrap head-row">
        <a class="brand" href="{{ $site->url }}" aria-label="{{ $site->name }}, {{ __('home') }}">@include('theme::partials.icon-mark'){{ $site->name }}</a>
        <div class="nav"><x-teme::navigation slot="primary" :label="__('Main')" /></div>
        <div class="head-end">
            @include('theme::partials.follow', ['class' => 'btn btn-quiet follow-wide'])
            <details class="menu">
                <summary class="menu-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4 7h16M4 12h16M4 17h16"/></svg><span class="vh">{{ __('Menu') }}</span></summary>
                <div class="mobile-nav">
                    <x-teme::navigation slot="primary" :label="__('Main, small screens')" />
                    @include('theme::partials.follow', ['class' => 'btn btn-primary', 'icon' => true])
                </div>
            </details>
        </div>
    </div>
</header>
