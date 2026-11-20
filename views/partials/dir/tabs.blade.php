{{--
    An item's tabs, given $tabs: a list of [id, label, count|null, view, data]. Without the theme's script every panel reads
    down the page under its own heading; with it, they become tabs.
--}}
<div class="tabs" role="tablist" aria-label="{{ $label }}" data-tabs hidden>
    @foreach ($tabs as $i => $tab)
        <button type="button" role="tab" id="t-{{ $tab['id'] }}" aria-controls="p-{{ $tab['id'] }}" aria-selected="{{ $i === 0 ? 'true' : 'false' }}" tabindex="{{ $i === 0 ? '0' : '-1' }}">{{ $tab['label'] }}@if ($tab['count'] !== null) <span class="n">{{ $tab['count'] }}</span>@endif</button>
    @endforeach
</div>
@foreach ($tabs as $tab)
    <section id="p-{{ $tab['id'] }}" class="tab-panel" aria-labelledby="h-{{ $tab['id'] }}" data-panel>
        <h2 class="panel-h" id="h-{{ $tab['id'] }}">{{ $tab['label'] }}</h2>
        @include($tab['view'], $tab['data'])
    </section>
@endforeach
