{{-- Over a page of any version but the latest: which version this is, and a link to the latest. Given $versions. --}}
@php
    $currentVersion = collect($versions)->firstWhere('current', true);
    $latestVersion = collect($versions)->firstWhere('latest', true);
@endphp
@if ($currentVersion !== null && ! $currentVersion['latest'] && $latestVersion !== null)
    <div class="ver-banner" role="note">{{ __('You are reading the documentation for :version.', ['version' => $currentVersion['label']]) }} <a href="{{ $latestVersion['url'] }}">{{ __('Read it for :latest, the latest', ['latest' => $latestVersion['label']]) }}</a></div>
@endif
