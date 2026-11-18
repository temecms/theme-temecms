{{-- The footer: who the site is and its maker in one plain line, three menus, then the status and licence lines, each left out when empty. No person's name, no address, no form. --}}
<footer class="site-foot">
    <div class="wrap">
        <div class="foot-grid">
            <div class="foot-about">
                <a class="brand" href="{{ $site->url }}" aria-label="{{ $site->name }}, {{ __('home') }}">@include('theme::partials.icon-mark'){{ $site->name }}</a>
                @if ($site->tagline !== '' || ($themeSettings['maker_line'] ?? '') !== '')
                    <p>{{ $site->tagline }} {{ $themeSettings['maker_line'] ?? '' }}</p>
                @endif
            </div>
            <div><h2>{{ __('Teme') }}</h2><x-teme::navigation slot="footer-teme" :label="__('Teme')" /></div>
            <div><h2>{{ __('Build') }}</h2><x-teme::navigation slot="footer-build" :label="__('Build')" /></div>
            <div><h2>{{ __('Project') }}</h2><x-teme::navigation slot="footer-project" :label="__('Project')" /></div>
        </div>
        <div class="foot-base">
            @if (($themeSettings['status_line'] ?? '') !== '')<span>{{ $themeSettings['status_line'] }}</span>@endif
            @if (($themeSettings['licence_line'] ?? '') !== '')<span>{{ $themeSettings['licence_line'] }}</span>@endif
        </div>
    </div>
</footer>
