{{-- The footer's one line, given $line (the theme's setting, words between asterisks in italics), or else $tagline and $maker. --}}
@php
    $footWords = trim($line) !== ''
        ? (string) preg_replace('/\*([^*]+)\*/', '<em>$1</em>', e(trim($line)))
        : e(trim(trim($tagline).' '.trim($maker)));
@endphp
@if ($footWords !== '')<p>{!! $footWords !!}</p>@endif
