{{-- A listing's icon tile, given $listing and an optional $size (sm or lg): its own icon image, else the glyph its field names, else its first letter. --}}
@php $size = $size ?? ''; $glyphs = ['send', 'search', 'layers', 'share', 'list2', 'braces']; @endphp
<span class="pi {{ $size }} @if ($listing['iconImage'] !== null) has-img @endif" style="--h:{{ $listing['hue'] }}" aria-hidden="true">
    @if ($listing['iconImage'] !== null)
        <x-teme::image :id="$listing['iconImage']->id" context="thumb" :decorative="true" />
    @elseif (in_array($listing['glyph'], $glyphs, true))
        @include('theme::partials.dir.glyph', ['icon' => $listing['glyph']])
    @else
        <span class="pi-letter">{{ mb_strtoupper(mb_substr($listing['title'], 0, 1)) }}</span>
    @endif
</span>
