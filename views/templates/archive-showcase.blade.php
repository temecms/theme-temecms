{{--
    The Showcase: real sites built with Teme, one large picture each, with its name, kind, address, the theme and plugins it
    runs (linked to their directory pages) and a line from its owner; filters by kind with counts, and "Submit your site".
    Sites are content of the temecms-site plugin's showcase type; only approved ones are published.
--}}
@extends('theme::layouts.base')

@section('main')
    @php
        $kinds = ['news' => __('News'), 'blog' => __('Blog'), 'documentation' => __('Documentation'), 'magazine' => __('Magazine')];
        $sites = array_map(function ($item) {
            $plugins = [];
            for ($n = 1; $n <= 8; $n++) {
                $f = $item->field('plugin_item_'.$n);
                if ($f !== null && ! $f->empty && $f->url !== null) {
                    $plugins[] = ['title' => $f->text, 'url' => $f->url];
                }
            }
            $theme = $item->field('theme_item');
            $address = trim((string) ($item->field('site_address')?->value ?? ''));
            $shot = $item->field('screenshot')?->image ?? $item->image;

            return [
                'title' => $item->title,
                'kind' => (string) ($item->field('site_kind')?->value ?? ''),
                'kindText' => (string) ($item->field('site_kind')?->text ?? ''),
                'address' => $address,
                'host' => $address !== '' ? (string) preg_replace('#^https?://(www\.)?#', '', rtrim($address, '/')) : '',
                'shot' => $shot,
                'theme' => ($theme !== null && ! $theme->empty && $theme->url !== null) ? ['title' => $theme->text, 'url' => $theme->url] : null,
                'plugins' => $plugins,
                'line' => trim((string) ($item->field('owner_line')?->value ?? '')),
                'since' => $item->published->format('Y'),
            ];
        }, $page->items);
        $counts = array_count_values(array_column($sites, 'kind'));
        foreach ($sites as $s) {
            if ($s['kind'] !== '' && ! isset($kinds[$s['kind']])) {
                $kinds[$s['kind']] = $s['kindText'] !== '' ? $s['kindText'] : ucfirst($s['kind']);
            }
        }
    @endphp
    <div class="dx"><div class="wrap" data-showcase>
        <div class="page-top">
            <div>
                <h1>{{ __('Showcase') }}</h1>
                <p>{{ __('Real sites built with Teme, with the theme and plugins each one runs.') }}</p>
            </div>
            <div class="end"><a class="btn btn-quiet" href="#submit-site" data-open-dlg>{{ __('Submit your site') }}</a></div>
        </div>

        <div class="chips" role="group" aria-label="{{ __('Filter by kind of site') }}" data-kind-chips hidden>
            <button type="button" class="chip" data-kind="all" aria-pressed="true">{{ __('All') }} <span class="n num">{{ count($sites) }}</span></button>
            @foreach ($kinds as $key => $words)
                <button type="button" class="chip" data-kind="{{ $key }}" data-kind-words="{{ mb_strtolower($words) }}" aria-pressed="false">{{ $words }} <span class="n num">{{ $counts[$key] ?? 0 }}</span></button>
            @endforeach
        </div>

        <div class="show-grid">
            @foreach ($sites as $show)
                <article class="show-card" data-site="{{ $show['kind'] }}">
                    @if ($show['shot'] !== null)
                        @if ($show['address'] !== '')<a class="bigshot" href="{{ $show['address'] }}" tabindex="-1" aria-hidden="true">@else<div class="bigshot">@endif
                            <x-teme::image :id="$show['shot']->id" context="card" :decorative="true" />
                        @if ($show['address'] !== '')</a>@else</div>@endif
                    @endif
                    <div>
                        <h2>{{ $show['title'] }} @if ($show['kindText'] !== '')<span class="tag">{{ $show['kindText'] }}</span>@endif</h2>
                        @if ($show['address'] !== '')<a class="addr" href="{{ $show['address'] }}">{{ $show['host'] }}</a>@endif
                    </div>
                    <dl>
                        <dt>{{ __('Theme') }}</dt><dd>@if ($show['theme'] !== null)<a href="{{ $show['theme']['url'] }}">{{ $show['theme']['title'] }}</a>@else<span class="muted">{{ __('Its own') }}</span>@endif</dd>
                        @if ($show['plugins'] !== [])<dt>{{ __('Plugins') }}</dt><dd>@foreach ($show['plugins'] as $p)<a href="{{ $p['url'] }}">{{ $p['title'] }}</a>{{ $loop->last ? '' : ', ' }}@endforeach</dd>@endif
                        <dt>{{ __('Since') }}</dt><dd>{{ $show['since'] }}</dd>
                    </dl>
                    @if ($show['line'] !== '')<blockquote>{{ $show['line'] }}</blockquote>@endif
                </article>
            @endforeach
        </div>

        <div class="empty" data-show-empty @if ($sites !== []) hidden @endif>
            <b data-empty-words data-template="{{ __('No :kind site here yet.') }}">{{ __('No site here yet.') }}</b>
            <p>{{ __('Built one with Teme? Send it; a person looks at each site before it is listed.') }}</p>
            <a class="btn btn-quiet" href="#submit-site" data-open-dlg>{{ __('Submit your site') }}</a>
        </div>

        <p class="muted small show-foot">{{ __('Sites are listed by hand. We list a site only when its owner asks, and remove it when they ask.') }}</p>
        @include('theme::partials.pagination', ['pagination' => $page->pagination])

        <section class="site-form" id="submit-site" aria-labelledby="ss-h" data-dlg>
            <form class="dlg-in" method="post" action="/temecms-site/submit-site">
                <div class="dlg-head"><div><h2 id="ss-h">{{ __('Submit your site') }}</h2><p>{{ __('A person looks at each site before it is listed.') }}</p></div><button type="button" class="close-x" data-close hidden aria-label="{{ __('Close') }}">@include('theme::partials.dir.glyph', ['icon' => 'x'])</button></div>
                <div class="done-box" role="status" data-submit-done hidden><b data-done-title></b><span data-done-line></span></div>
                <div class="err-box" role="alert" data-submit-error hidden></div>
                <div class="fld"><label for="ss-name">{{ __('Site name') }}</label><input class="input" id="ss-name" name="name" type="text" required></div>
                <div class="fld"><label for="ss-url">{{ __('Site address') }}</label><input class="input" id="ss-url" name="address" type="url" placeholder="https://example.com" required></div>
                <div class="fld"><label for="ss-kind">{{ __('Kind') }}</label><select class="select" id="ss-kind" name="kind">@foreach (['news' => __('News'), 'blog' => __('Blog'), 'documentation' => __('Documentation'), 'magazine' => __('Magazine'), 'other' => __('Other')] as $key => $words)<option value="{{ $key }}">{{ $words }}</option>@endforeach</select></div>
                <div class="fld"><label for="ss-line">{{ __('A short line from you') }} <span class="muted">{{ __('(optional)') }}</span></label><input class="input" id="ss-line" name="line" maxlength="140" placeholder="{{ __('What the site is, in one sentence') }}"></div>
                <div class="fld"><label for="ss-mail">{{ __('Contact email') }}</label><input class="input" id="ss-mail" name="contact" type="email" autocomplete="email" required aria-describedby="ss-mail-hint"><p class="hint" id="ss-mail-hint">{{ __('Only to ask you about the listing.') }}</p></div>
                <div class="hp" aria-hidden="true"><label for="ss-website">{{ __('Leave this empty') }}</label><input id="ss-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
                <div class="form-foot"><button type="submit" class="btn btn-primary">{{ __('Send') }}</button></div>
            </form>
        </section>
    </div></div>
@endsection
