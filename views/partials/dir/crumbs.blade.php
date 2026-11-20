{{-- An item's trail: its directory, its main category, and the item. Given $listing, $dirName, $dirUrl. --}}
<nav class="crumbs" aria-label="{{ __('Breadcrumb') }}">
    @if ($dirUrl !== '')<a href="{{ $dirUrl }}">{{ $dirName }}</a>@else<span>{{ $dirName }}</span>@endif<span aria-hidden="true">/</span>
    @if ($listing['categories'] !== [] && ($catBase ?? '') !== '')<a href="{{ $catBase }}#category={{ $listing['categories'][0] }}">{{ $listing['categoryLabels'][$listing['categories'][0]] }}</a><span aria-hidden="true">/</span>@endif
    <span aria-current="page">{{ $listing['title'] }}</span>
</nav>
