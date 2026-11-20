{{-- Every page's frame: core's document, then the site's header, the page's one <main>, and the footer. The tab's icon is the theme's own: the SVG switches to its dark tones with the reader's colour scheme, and the PNG is for browsers without SVG icons. --}}
@push('teme-head')
    <link rel="icon" href="{{ asset('themes/temecms/icon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('themes/temecms/icon-32.png') }}" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('themes/temecms/icon-180.png') }}">
@endpush
<x-teme::document>
    @include('theme::partials.site-header')
    <main id="main" tabindex="-1">
        @yield('main')
    </main>
    @include('theme::partials.site-footer')
</x-teme::document>
