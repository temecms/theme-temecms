{{-- The documentation's tree: the version switcher, then each group (open when it holds the page, and on wide screens), each nested group closed unless it holds the page. Given $groups, $versions, $versionLabel. --}}
<nav class="docs-tree" id="docs-tree" aria-label="{{ __('Documentation') }}" data-docs-tree>
    <div class="tree-close"><span>{{ __('Documentation') }}</span><button type="button" class="close-x" data-tree-close aria-label="{{ __('Close the menu') }}">@include('theme::partials.dir.glyph', ['icon' => 'x'])</button></div>
    @if ($versions !== [])
        <div class="tree-top"><label class="ver" for="docs-ver"><span>{{ __('Version') }}</span></label>
            <select class="select" id="docs-ver" data-docs-ver>@foreach ($versions as $v)<option value="{{ $v['url'] }}" @selected($v['current'])>{{ $v['latest'] ? __(':v (latest)', ['v' => $v['label']]) : $v['label'] }}</option>@endforeach</select>
        </div>
    @elseif ($versionLabel !== '')
        <div class="tree-top"><span class="ver"><span>{{ __('Version') }}</span> <b>{{ $versionLabel }}</b></span></div>
    @endif
    @foreach ($groups as $g)
        <details class="tree-group" open>
            <summary>{{ $g['label'] }}@include('theme::partials.dir.glyph', ['icon' => 'chev'])</summary>
            @include('theme::partials.dir.docs-tree-items', ['items' => $g['items']])
        </details>
    @endforeach
</nav>
