{{-- The docs search: a dialog the header's search button and Ctrl K (or / ) open, searching the guides' titles, summaries and this page's headings as you type, grouped by section. Its list comes from $index (title, url, summary, group). --}}
<div class="sov" data-sov data-index="{{ json_encode($index, JSON_UNESCAPED_UNICODE) }}" data-src="{{ $indexSrc }}" data-site-search-url="{{ $siteSearch }}" role="dialog" aria-modal="true" aria-labelledby="sov-h" hidden>
    <div class="sov-box">
        <h2 class="vh" id="sov-h">{{ __('Search the docs') }}</h2>
        <div class="sov-in">@include('theme::partials.dir.glyph', ['icon' => 'search'])<label class="vh" for="sov-q">{{ __('Search the docs') }}</label><input id="sov-q" type="search" autocomplete="off" placeholder="{{ __('Search the docs') }}" role="combobox" aria-expanded="true" aria-controls="sov-res" aria-autocomplete="list" data-sov-q><button type="button" class="close-x" data-sov-close>{{ __('Esc') }}</button></div>
        <div class="sov-res" id="sov-res" role="listbox" aria-label="{{ __('Results') }}" data-sov-res data-none="{{ __('Nothing in the docs matches “:q”.') }}" data-site-search="{{ __('Search the whole site for it') }}"></div>
        <div class="sov-foot"><span><kbd>↑</kbd><kbd>↓</kbd> {{ __('to move') }}</span><span><kbd>↵</kbd> {{ __('to open') }}</span><span><kbd>Esc</kbd> {{ __('to close') }}</span></div>
    </div>
</div>
