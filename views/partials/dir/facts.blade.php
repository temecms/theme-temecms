{{-- An item's facts column: the review line, the labelled facts, ratings later, the author with "More from", and the links. Given $listing, $isTheme. --}}
@php
    $latest = $listing['releases'] !== [] ? $listing['releases'][0] : null;
    foreach ($listing['releases'] as $r) {
        if ($latest === null || version_compare((string) ($r['version'] ?? '0'), (string) ($latest['version'] ?? '0'), '>')) {
            $latest = $r;
        }
    }
    $releaseDate = null;
    if (is_string($latest['date'] ?? null) && ($t = strtotime($latest['date'])) !== false) {
        $releaseDate = (new DateTimeImmutable())->setTimestamp($t);
    }
    $releaseDate ??= $listing['updated'];
    $releases = count($listing['releases']);
    $initials = collect(preg_split('/\s+/', trim($listing['author'])) ?: [])->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
@endphp
<div class="facts-col">
    @if ($listing['version'] !== '')
        <div class="reviewed">@include('theme::partials.dir.glyph', ['icon' => 'shield'])<div><b>{{ __('Reviewed by Teme') }}</b>
            @if ($releaseDate !== null){{ __(':version approved on :date.', ['version' => $listing['version'], 'date' => $releaseDate->format('j M Y')]) }}@endif
            {{ __('Checks passed') }}@if ($listing['listed'] !== null){{ __('; a person approved the author and the first release on :date.', ['date' => $listing['listed']->format('j M Y')]) }}@else.@endif
        </div></div>
    @endif
    <dl class="facts box" aria-label="{{ __('Facts') }}">
        @if ($listing['version'] !== '')<div><dt>{{ __('Version') }}</dt><dd><span class="num">{{ $listing['version'] }}</span> <span class="tag">{{ __('Latest') }}</span></dd></div>@endif
        @if ($listing['updated'] !== null)<div><dt>{{ __('Last updated') }}</dt><dd>{{ $listing['updated']->format('j M Y') }}<small><time datetime="{{ $listing['updated']->format('Y-m-d') }}" data-rel data-rel-only></time></small></dd></div>@endif
        @if ($releases > 0)<div><dt>{{ __('Releases') }}</dt><dd>{{ trans_choice(':count release|:count releases', $releases, ['count' => $releases]) }}@if ($listing['listed'] !== null)<small>{{ __('since it was listed on :date', ['date' => $listing['listed']->format('j M Y')]) }}</small>@endif</dd></div>@endif
        @if ($listing['works'] !== [])<div><dt>{{ __('Works with') }}</dt><dd><span class="works">@include('theme::partials.dir.glyph', ['icon' => 'check']){{ __('Works with :what', ['what' => temecms_works_short($listing)]) }}</span>@if (count($listing['works']) > 1)<small>{{ temecms_works_range($listing) }}</small>@endif</dd></div>@endif
        @if ($listing['categories'] !== [])<div><dt>{{ __('Categories') }}</dt><dd class="cats">@foreach ($listing['categories'] as $key)@if (($catBase ?? '') !== '')<a href="{{ $catBase }}#category={{ $key }}">{{ $listing['categoryLabels'][$key] }}</a>@else{{ $listing['categoryLabels'][$key] }}@endif{{ $loop->last ? '' : ', ' }}@endforeach</dd></div>@endif
        @if ($listing['package'] !== '')<div><dt>{{ __('Package') }}</dt><dd><code>{{ $listing['package'] }}</code></dd></div>@endif
        @if ($listing['stars'] !== null || $listing['issues'] !== null)
            <div><dt>{{ __('GitHub') }}</dt><dd><div class="gh-stats">
                @if ($listing['stars'] !== null)<span>@include('theme::partials.dir.glyph', ['icon' => 'star'])<b class="num">{{ $listing['stars'] }}</b> {{ trans_choice('star|stars', (int) $listing['stars']) }}</span>@endif
                @if ($listing['issues'] !== null)<span>@include('theme::partials.dir.glyph', ['icon' => 'bug'])<b class="num">{{ $listing['issues'] }}</b> {{ trans_choice('open issue|open issues', (int) $listing['issues']) }}</span>@endif
            </div></dd></div>
        @endif
    </dl>
    <div class="later" role="note"><b>{{ __('Ratings and reviews: later') }}</b>{{ __('Not in the first release. This space is kept for them.') }}</div>
    @if ($listing['author'] !== '')
        <div class="box" data-author-box data-author="{{ $listing['author'] }}" data-self="{{ $listing['url'] }}" data-from="{{ $isTheme ? ($themeSettings['themes_address'] ?? '') : ($themeSettings['plugins_address'] ?? '') }}">
            <h2>{{ __('Author') }}</h2>
            <div class="author-row"><span class="avatar" aria-hidden="true">{{ $initials }}</span><div><b>@if ($listing['authorUrl'] !== '')<a href="{{ $listing['authorUrl'] }}">{{ $listing['author'] }}</a>@else{{ $listing['author'] }}@endif</b>@if ($listing['github'] !== '')<small>{{ '@'.$listing['github'] }}</small>@endif</div></div>
            <div data-more hidden><h3 class="more-h">{{ __('More from :name', ['name' => $listing['author']]) }}</h3><ul class="more-list" data-more-list></ul></div>
        </div>
    @endif
    <ul class="links box">
        @if ($listing['links']['source'] !== '')<li><a href="{{ $listing['links']['source'] }}">@include('theme::partials.dir.glyph', ['icon' => 'github']){{ __('Source on GitHub') }}</a></li>@endif
        @if ($listing['links']['docs'] !== '')<li><a href="{{ $listing['links']['docs'] }}">@include('theme::partials.dir.glyph', ['icon' => 'book']){{ __('Documentation') }}</a></li>@endif
        <li><a class="report" href="{{ $listing['report'] }}">@include('theme::partials.dir.glyph', ['icon' => 'flag']){{ $isTheme ? __('Report this theme') : __('Report this listing') }}</a></li>
    </ul>
</div>
