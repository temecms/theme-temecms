{{-- The foot of a docs page: previous and next from the tree, Edit this page, when it was checked, and "Was this page helpful?". Given $prev, $next, $editUrl, $checkedLine, $feedbackUrl. --}}
<div class="doc-foot">
    @if ($prev !== null || $next !== null)
        <nav class="pnext" aria-label="{{ __('Previous and next page') }}">
            @if ($prev !== null)<a class="prev" href="{{ $prev['url'] }}"><small>{{ __('Previous') }}</small><b>{{ $prev['label'] }}</b></a>@else<span></span>@endif
            @if ($next !== null)<a class="next" href="{{ $next['url'] }}"><small>{{ __('Next') }}</small><b>{{ $next['label'] }}</b></a>@endif
        </nav>
    @endif
    <div class="doc-meta">
        @if ($editUrl !== '')<a href="{{ $editUrl }}">@include('theme::partials.dir.glyph', ['icon' => 'edit']){{ __('Edit this page on GitHub') }}</a>@endif
        @if ($checkedLine !== '')<span>{{ $checkedLine }}</span>@endif
        <div class="helpful" role="group" aria-labelledby="hp-q" data-helpful hidden>
            <p id="hp-q">{{ __('Was this page helpful?') }}</p>
            <button type="button" data-answer="yes" aria-pressed="false">{{ __('Yes') }}</button>
            <button type="button" data-answer="no" aria-pressed="false">{{ __('No') }}</button>
            <p class="thanks" role="status" data-thanks hidden data-yes="{{ __('Thanks. Glad it helped.') }}" data-no="{{ __('Thanks. Tell us what was missing:') }}">@if ($feedbackUrl !== '')<a href="{{ $feedbackUrl }}" hidden data-no-link>{{ __('open an issue on GitHub') }}</a>@endif</p>
        </div>
    </div>
</div>
