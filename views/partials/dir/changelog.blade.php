{{-- An item's changelog, from the CHANGELOG.md the directory read, as cleaned Markdown, then every release with its date and the Teme versions it works with. Given $listing. --}}
@php
    $markdown = preg_replace('/\A\s*#\s[^\n]*\n/', '', $listing['changelog']) ?? '';
    $html = $markdown !== '' ? (string) \Illuminate\Support\Str::markdown($markdown, ['html_input' => 'strip', 'allow_unsafe_links' => false]) : '';
    $html = preg_replace(['/<h2(\s|>)/', '/<\/h2>/', '/<h1(\s|>)/', '/<\/h1>/'], ['<h3$1', '</h3>', '<h3$1', '</h3>'], $html) ?? '';
@endphp
@if ($html !== '')
    <div class="changelog prose">{!! $html !!}</div>
@else
    <p class="muted">{{ __('No changelog was given with this package.') }}</p>
@endif
@if (count($listing['releases']) > 0)
    <details class="all-releases">
        <summary class="cl-more">{{ trans_choice('All :count version, with its Teme range|All :count versions, with their Teme ranges', count($listing['releases']), ['count' => count($listing['releases'])]) }}</summary>
        <table class="rel-table"><thead><tr><th scope="col">{{ __('Version') }}</th><th scope="col">{{ __('Date') }}</th><th scope="col">{{ __('Works with') }}</th></tr></thead><tbody>
            @foreach ($listing['releases'] as $r)
                <tr><td class="num">{{ $r['version'] ?? '' }}</td><td>{{ is_string($r['date'] ?? null) ? $r['date'] : '' }}</td><td>{{ is_array($r['works_with'] ?? null) ? implode(', ', $r['works_with']) : (string) ($r['works_with'] ?? '') }}</td></tr>
            @endforeach
        </tbody></table>
    </details>
@endif
