{{-- Every page's frame: core's document, then the site's header, the page's one <main>, and the footer. --}}
<x-teme::document>
    @include('theme::partials.site-header')
    <main id="main" tabindex="-1">
        @yield('main')
    </main>
    @include('theme::partials.site-footer')
</x-teme::document>
