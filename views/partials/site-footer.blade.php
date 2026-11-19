{{-- The footer: who the site is and its maker in one plain line, three menus, then the status line on the left and the licence on the right, each left out when it's empty. No person's name, no address, no form. --}}
<footer class="site-foot">
    <div class="wrap">
        <div class="foot-grid">
            <div class="foot-about">
                <a class="brand" href="{{ $site->url }}" aria-label="{{ $site->name }}, {{ __('home') }}">@include('theme::partials.icon-mark'){{ $site->name }}</a>
                @include('theme::partials.foot-words', ['line' => (string) ($themeSettings['footer_line'] ?? ''), 'tagline' => $site->tagline, 'maker' => (string) ($themeSettings['maker_line'] ?? '')])
            </div>
            <div><h2>{{ __('Teme') }}</h2><x-teme::navigation slot="footer-teme" :label="__('Teme')" /></div>
            <div><h2>{{ __('Build') }}</h2><x-teme::navigation slot="footer-build" :label="__('Build')" /></div>
            <div><h2>{{ __('Project') }}</h2><x-teme::navigation slot="footer-project" :label="__('Project')" /></div>
        </div>
        @include('theme::partials.foot-base', ['status' => (string) ($themeSettings['status_line'] ?? ''), 'licence' => (string) ($themeSettings['licence_line'] ?? '')])
    </div>
</footer>
