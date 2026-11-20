{{-- "On this page" above the article, for narrower screens, given $toc. --}}
@if ($toc !== [])
    <details class="otp-inline"><summary>{{ __('On this page') }}@include('theme::partials.dir.glyph', ['icon' => 'chev'])</summary><ul>@foreach ($toc as $t)<li><a href="#{{ $t['id'] }}">{{ $t['text'] }}</a></li>@endforeach</ul></details>
@endif
