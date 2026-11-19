{{-- "On this page" beside the article, given $toc: the page's h2 headings, the one being read marked by the theme's script. --}}
@if ($toc !== [])
    <aside class="otp" aria-labelledby="otp-h">
        <p class="otp-h" id="otp-h">{{ __('On this page') }}</p>
        <ul>@foreach ($toc as $t)<li><a href="#{{ $t['id'] }}">{{ $t['text'] }}</a></li>@endforeach</ul>
        <a class="back-top" href="#top">{{ __('Back to the top') }}</a>
    </aside>
@else
    <div class="otp-none"></div>
@endif
