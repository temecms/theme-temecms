{{-- A theme's card in the directory: its picture first, then name, author and the same facts line as a plugin's card. --}}
@php $shot = $listing['screenshots'][0]['media'] ?? $listing['image']?->id; @endphp
<a class="tcard" href="{{ $listing['url'] }}" data-item
   data-name="{{ $listing['title'] }}" data-author="{{ $listing['author'] }}" data-cats="{{ implode(' ', $listing['categories']) }}"
   data-works="{{ implode(' ', $listing['works']) }}" data-updated="{{ $listing['updated']?->format('Y-m-d') }}" data-listed="{{ $listing['listed']?->format('Y-m-d') }}">
    <div class="shotbox">@if ($shot !== null)<x-teme::image :id="$shot" context="card" :decorative="true" />@else<span class="shot-none">{{ __('No picture yet') }}</span>@endif</div>
    <div><h3>{{ $listing['title'] }}</h3>@if ($listing['author'] !== '')<p class="muted small">{{ __('by :name', ['name' => $listing['author']]) }}</p>@endif</div>
    <div class="meta">
        @if ($listing['version'] !== '')<span class="num">v{{ $listing['version'] }}</span>@endif
        @if ($listing['works'] !== [])<span class="works">@include('theme::partials.dir.glyph', ['icon' => 'check']){{ temecms_works_short($listing) }}</span>@endif
        @if ($listing['updated'] !== null)<span>{{ __('Updated') }} @include('theme::partials.dir.date', ['date' => $listing['updated'], 'relative' => true])</span>@endif
        <span>{{ __('Free') }}</span>
    </div>
</a>
