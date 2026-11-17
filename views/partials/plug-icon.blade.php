{{-- A plugin's small icon, given its key ($icon): the drawing's own shapes, in the tone that goes with it. --}}
@php
    $paths = [
        'send' => '<path d="m4 12 16-8-6 16-2-7Z"/><path d="m12 13 8-9"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'layers' => '<path d="m12 3 9 5-9 5-9-5Z"/><path d="m3 13 9 5 9-5"/>',
        'share' => '<circle cx="6" cy="12" r="2.5"/><circle cx="18" cy="6" r="2.5"/><circle cx="18" cy="18" r="2.5"/><path d="m8.2 10.8 7.6-3.6M8.2 13.2l7.6 3.6"/>',
        'list2' => '<path d="M10 6h10M10 12h10M10 18h10"/><path d="m3.5 6 1.5 1.5L7.5 5M3.5 12l1.5 1.5L7.5 11M3.5 18l1.5 1.5L7.5 17"/>',
        'braces' => '<path d="M8 4H7a2 2 0 0 0-2 2v4a2 2 0 0 1-2 2 2 2 0 0 1 2 2v4a2 2 0 0 0 2 2h1M16 4h1a2 2 0 0 1 2 2v4a2 2 0 0 0 2 2 2 2 0 0 0-2 2v4a2 2 0 0 1-2 2h-1"/>',
    ];
    $tones = ['search' => 'i3', 'layers' => 'i2', 'share' => 'i5', 'list2' => 'i4', 'braces' => 'i6'];
@endphp
<span class="plug-ico {{ $tones[$icon] ?? '' }}" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" focusable="false">{!! $paths[$icon] ?? $paths['layers'] !!}</svg></span>
