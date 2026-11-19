{{-- The release the documentation describes: the docs_version setting, or else the Teme release the site runs ($teme['release'], from a Teme new enough to give it); nothing when neither is there. --}}
@php
    $docsVersion = trim((string) ($themeSettings['docs_version'] ?? ''));
    if ($docsVersion === '' && is_array($teme ?? null)) {
        $docsVersion = trim((string) ($teme['release'] ?? ''));
    }
@endphp
<div class="end">@if ($docsVersion !== '')<span class="ver"><span>{{ __('Version') }}</span> <b>{{ $docsVersion }}</b></span>@endif</div>
