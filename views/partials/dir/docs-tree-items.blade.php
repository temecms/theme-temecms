{{-- One level of the documentation's tree, given $items: links, and nested groups that open when they hold the page. --}}
<ul>
    @foreach ($items as $it)
        @if (($it['children'] ?? []) !== [])
            @php $here = (bool) ($it['current'] ?? false) || str_contains(json_encode($it['children']) ?: '', '"current":true'); @endphp
            <li><details class="tree-sub" @if ($here) open @endif><summary>{{ $it['label'] }}@include('theme::partials.dir.glyph', ['icon' => 'chev'])</summary>
                @include('theme::partials.dir.docs-tree-items', ['items' => $it['children']])
            </details></li>
        @elseif (($it['url'] ?? null) !== null)
            <li><a href="{{ $it['url'] }}" @if ($it['current'] ?? false) aria-current="page" @endif>{{ $it['label'] }}</a></li>
        @else
            <li><span class="tree-h">{{ $it['label'] }}</span></li>
        @endif
    @endforeach
</ul>
