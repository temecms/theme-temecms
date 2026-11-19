{{-- A date as a <time>: the day in words, which the theme's script may turn into "2 days ago" when $relative is set. Nothing for no date. --}}
@if ($date !== null)<time datetime="{{ $date->format('Y-m-d') }}" @if ($relative ?? false) data-rel @endif>{{ $date->format('j M Y') }}</time>@endif
