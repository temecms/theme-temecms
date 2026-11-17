{{-- A breadcrumb trail, given $crumbs (a list of Crumb): every step a link but the last, which is the page. --}}
<nav class="crumbs" aria-label="{{ __('Breadcrumb') }}">
    @foreach ($crumbs as $crumb)
        @if ($loop->last)
            <b aria-current="page">{{ $crumb->name }}</b>
        @else
            <a href="{{ $crumb->url }}">{{ $crumb->name }}</a><span aria-hidden="true">/</span>
        @endif
    @endforeach
</nav>
