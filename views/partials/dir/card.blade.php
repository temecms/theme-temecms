{{-- A plugin's card in the directory: icon, name, author, summary, version, works with and last updated; the whole card is one link. Its data-* attributes are what the directory's script filters and sorts by. --}}
<a class="card" href="{{ $listing['url'] }}" data-item
   data-name="{{ $listing['title'] }}" data-author="{{ $listing['author'] }}" data-cats="{{ implode(' ', $listing['categories']) }}"
   data-works="{{ implode(' ', $listing['works']) }}" data-updated="{{ $listing['updated']?->format('Y-m-d') }}" data-listed="{{ $listing['listed']?->format('Y-m-d') }}">
    <div class="card-top">
        @include('theme::partials.dir.icon', ['listing' => $listing])
        <div><h3>{{ $listing['title'] }}</h3>@if ($listing['author'] !== '')<p class="by">{{ __('by :name', ['name' => $listing['author']]) }}</p>@endif</div>
        <span class="price tag">{{ __('Free') }}</span>
    </div>
    @if ($listing['summary'] !== '')<p class="sum">{{ $listing['summary'] }}</p>@endif
    <div class="card-facts">
        @if ($listing['version'] !== '')<span class="num">v{{ $listing['version'] }}</span>@endif
        @if ($listing['works'] !== [])<span class="works">@include('theme::partials.dir.glyph', ['icon' => 'check']){{ temecms_works_short($listing) }}</span>@endif
        @if ($listing['updated'] !== null)<span>{{ __('Updated') }} @include('theme::partials.dir.date', ['date' => $listing['updated'], 'relative' => true])</span>@endif
    </div>
</a>
