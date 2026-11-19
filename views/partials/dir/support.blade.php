{{-- Where to get help with an item: its issues, documentation and support links, and reporting the listing. Given $listing. --}}
<div class="support-grid">
    @if ($listing['links']['issues'] !== '')<a href="{{ $listing['links']['issues'] }}"><b>{{ __('Issues on GitHub') }}</b><small>@if ($listing['issues'] !== null){{ trans_choice(':count open.|:count open.', (int) $listing['issues'], ['count' => $listing['issues']]) }} @endif{{ __('Bugs and questions go here.') }}</small></a>@endif
    @if ($listing['links']['docs'] !== '')<a href="{{ $listing['links']['docs'] }}"><b>{{ __('Documentation') }}</b><small>{{ __('The author’s guide, from the manifest’s docs link.') }}</small></a>@endif
    @if ($listing['links']['support'] !== '')<a href="{{ $listing['links']['support'] }}"><b>{{ __('Support') }}</b><small>{{ __('Where the author answers questions.') }}</small></a>@endif
    <a href="{{ $listing['report'] }}"><b>{{ __('Report this listing') }}</b><small>{{ __('To Teme’s reviewers: something here is wrong or unsafe.') }}</small></a>
</div>
