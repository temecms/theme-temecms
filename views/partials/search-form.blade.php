{{-- A plain GET search, given its field's own id ($id), its value and placeholder: a page may draw several. --}}
<form class="field {{ $class ?? 'docs-search' }}" role="search" action="{{ $site->url }}" method="get">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
    <label class="vh" for="{{ $id }}">{{ $label ?? __('Search the site') }}</label>
    <input id="{{ $id }}" name="s" type="search" value="{{ $value ?? '' }}" placeholder="{{ $placeholder ?? __('Search') }}" autocomplete="off"@if ($filter ?? false) data-filter-input @endif>
</form>
