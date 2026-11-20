{{-- An item's screenshots: a large stage with its caption and a row of thumbnails, which the theme's script turns into a gallery; without it they read down the page. Given $listing. --}}
@php $shots = $listing['screenshots']; $total = count($shots); @endphp
@if ($total === 0)
    <p class="muted">{{ __('No screenshots were given with this package.') }}</p>
@else
    <div class="gallery" data-gallery>
        @foreach ($shots as $i => $shot)
            <figure class="stage" data-stage="{{ $i }}">
                <div class="shot-img"><x-teme::image :id="$shot['media']" context="shot" :alt="(string) ($shot['caption'] ?? '')" :priority="false" /></div>
                @if (($shot['caption'] ?? '') !== '')<figcaption>{{ $shot['caption'] }}</figcaption>@endif
            </figure>
        @endforeach
        @if ($total > 1)
            <div class="thumbs" role="group" aria-label="{{ __('Screenshots') }}" data-thumbs hidden>
                @foreach ($shots as $i => $shot)
                    <button type="button" data-shot="{{ $i }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}" aria-label="{{ __('Screenshot :n: :caption', ['n' => $i + 1, 'caption' => $shot['caption'] ?? '']) }}"><span class="mini"><x-teme::image :id="$shot['media']" context="thumb" :decorative="true" /></span><span>{{ __(':n of :total', ['n' => $i + 1, 'total' => $total]) }}</span></button>
                @endforeach
            </div>
        @endif
    </div>
@endif
