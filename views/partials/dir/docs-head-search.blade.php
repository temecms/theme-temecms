{{-- The docs' search button in the site's header, with its shortcut shown. --}}
<button class="head-search" type="button" data-open-search hidden aria-keyshortcuts="Control+K">@include('theme::partials.dir.glyph', ['icon' => 'search'])<span class="hs-label">{{ __('Search the docs') }}</span><span class="keys" aria-hidden="true"><kbd data-mod>Ctrl</kbd><kbd>K</kbd></span></button>
