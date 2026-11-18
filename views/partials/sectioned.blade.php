{{--
    A page drawn as an opening and sections. Tiptap content is a flat list of blocks, so the page's body (already HTML,
    drawn by core) is split at its horizontal rules: the first part is the opening, inside the hero under the title and
    the standfirst; every later part is a section. What a section holds decides its layout:
      - a paragraph that is only bold text is the small label above the section's heading;
      - a paragraph that is only a bold link is a quiet button; core's button cards are buttons;
      - a picture beside text is a "stop"; headings (h3) followed by paragraphs are cards; a numbered list is steps.
    Given $page (an ArticleView) and $variant: home, landing, faq or roadmap.
--}}
@php
    libxml_use_internal_errors(true);
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->loadHTML('<?xml encoding="utf-8" ?><body>'.$page->bodyHtml.'</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    $bodyEl = $dom->getElementsByTagName('body')->item(0);
    foreach ($dom->getElementsByTagName('pre') as $pre) {
        $pre->setAttribute('tabindex', '0');
    }

    $sections = [[]];
    foreach ($bodyEl->childNodes as $node) {
        if ($node instanceof DOMText && trim($node->textContent) === '') {
            continue;
        }
        if ($node instanceof DOMElement && $node->tagName === 'hr') {
            $sections[] = [];
            continue;
        }
        $sections[array_key_last($sections)][] = $node;
    }

    $tag = static fn ($n): string => $n instanceof DOMElement ? $n->tagName : '';
    $cls = static fn ($n): string => $n instanceof DOMElement ? $n->getAttribute('class') : '';
    $html = static fn ($n): string => $n->ownerDocument->saveHTML($n);
    $loneStrong = static fn ($n): bool => $n instanceof DOMElement && $n->tagName === 'p' && $n->childNodes->length === 1 && $n->firstChild instanceof DOMElement && $n->firstChild->tagName === 'strong';
    $isKicker = static fn ($n): bool => $loneStrong($n) && $n->getElementsByTagName('a')->length === 0;
    $isCta = static fn ($n): bool => $loneStrong($n) && $n->getElementsByTagName('a')->length === 1;
    $isButton = static fn ($n): bool => $n instanceof DOMElement && $n->tagName === 'a' && str_contains($n->getAttribute('class'), 'card-button');
    $isFigure = static fn ($n): bool => $n instanceof DOMElement && $n->tagName === 'figure' && str_contains($n->getAttribute('class'), 'card-image');
    $slug = static fn (string $t): string => trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($t)), '-');
    // A button, as the site draws it: from a core button card, or from a paragraph that is only a bold link.
    $asButton = static function ($n) use ($isButton, $isCta, $dom): string {
        if ($isButton($n)) {
            $n->setAttribute('class', str_contains($n->getAttribute('class'), '--secondary') ? 'btn btn-quiet' : 'btn btn-primary');
            $href = $n->getAttribute('href');
            if (str_starts_with($href, 'https://')) {
                $n->setAttribute('target', '_blank');
                $n->setAttribute('rel', 'noopener');
            }

            return $dom->saveHTML($n);
        }
        $a = $n->getElementsByTagName('a')->item(0);
        $a->setAttribute('class', 'btn btn-quiet');
        if (str_starts_with($a->getAttribute('href'), 'https://')) {
            $a->setAttribute('target', '_blank');
            $a->setAttribute('rel', 'noopener');
        }

        return $dom->saveHTML($a);
    };
    $inner = static function ($n) use ($dom): string {
        $out = '';
        foreach ($n->childNodes as $c) {
            $out .= $dom->saveHTML($c);
        }

        return $out;
    };
    $withId = static function ($n) use ($slug): string {
        if (! $n->hasAttribute('id')) {
            $n->setAttribute('id', $slug($n->textContent));
        }

        return $n->ownerDocument->saveHTML($n);
    };

    $opening = array_shift($sections);
    $followLabel = trim((string) ($themeSettings['follow_label'] ?? '')) ?: __('Follow the build');
    $emphasis = (string) ($page->field('emphasis') ?? '');
    $kickerText = (string) ($page->field('kicker') ?? '');
    $title = e($page->title);
    if ($emphasis !== '' && str_contains($page->title, $emphasis)) {
        $title = e(substr($page->title, 0, strrpos($page->title, $emphasis))).'<em>'.e($emphasis).'</em>';
    }
@endphp

{{-- ===== the opening, in the hero ===== --}}
<section class="{{ $variant === 'home' ? 'o1-hero' : 'page-hero' }}" aria-labelledby="page-title">
    <div class="wrap">
        @if ($variant !== 'home' && $page->breadcrumbs !== null && count($page->breadcrumbs) > 1)
            @include('theme::partials.crumbs', ['crumbs' => $page->breadcrumbs])
        @endif
        @if ($kickerText !== '' && $variant !== 'home')<span class="kicker">{{ $kickerText }}</span>@endif
        @if (($icon ?? null) !== null)
            <div class="item-hero">
                @include('theme::partials.plug-icon', ['icon' => $icon])
                <div>
                    <h1 id="page-title">{!! $title !!}</h1>
                    @if ($page->standfirst !== null && $page->standfirst !== '')<p class="lead">{{ $page->standfirst }}</p>@endif
                    @if (($tags ?? []) !== [])<div class="tags">@foreach ($tags as $t)<span class="tag">{{ $t }}</span>@endforeach</div>@endif
                </div>
            </div>
        @else
            <h1 id="page-title">{!! $title !!}</h1>
            @if ($page->standfirst !== null && $page->standfirst !== '')
                <p class="{{ $variant === 'home' ? 'o1-lead' : 'lead' }}">{{ $page->standfirst }}</p>
            @endif
        @endif

        @php
            $run = [];
            $flush = function () use (&$run, $asButton) {
                if ($run === []) {
                    return '';
                }
                $buttons = implode('', array_map($asButton, $run));
                $run = [];

                return '<div class="cta-row">'.$buttons.'</div>';
            };
        @endphp
        @foreach ($opening as $node)
            @if ($isButton($node) || $isCta($node))
                @php $run[] = $node; @endphp
            @else
                {!! $flush() !!}
                @if ($isFigure($node))
                    <div class="{{ $variant === 'home' ? 'o1-heroshot' : 'heroshot' }}">{!! $html($node) !!}</div>
                @elseif ($tag($node) === 'ul' && $node->getElementsByTagName('li')->length > 0 && collect(iterator_to_array($node->getElementsByTagName('li')))->every(fn ($li) => ($li->getElementsByTagName('p')->item(0)?->firstChild ?? $li->firstChild) instanceof DOMElement && ($li->getElementsByTagName('p')->item(0)?->firstChild ?? $li->firstChild)->tagName === 'strong'))
                    <dl class="facts-strip">
                        @foreach ($node->getElementsByTagName('li') as $li)
                            @php
                                $holder = $li->getElementsByTagName('p')->item(0) ?? $li;
                                $factLabel = $holder->firstChild->textContent;
                                $holder->removeChild($holder->firstChild);
                            @endphp
                            <div><dt>{{ $factLabel }}</dt><dd>{!! trim($inner($holder)) !!}</dd></div>
                        @endforeach
                    </dl>
                @elseif ($cls($node) !== '' && str_contains($cls($node), 'card-callout'))
                    <div class="banner" role="note">{!! $inner($node) !!}</div>
                @else
                    {!! $html($node) !!}
                @endif
            @endif
        @endforeach
        {!! $flush() !!}
        @if ($variant === 'faq')
            <div class="faq-tools" hidden><button class="btn btn-quiet" type="button" data-faq="open">{{ __('Open all') }}</button><button class="btn btn-quiet" type="button" data-faq="close">{{ __('Close all') }}</button></div>
        @endif
        @if ($variant === 'home' && trim((string) ($themeSettings['status_line'] ?? '')) !== '')
            <p class="status-line"><b>{{ strtok((string) ($themeSettings['status_line'] ?? ''), '·') }}</b>@foreach (array_slice(array_map('trim', explode('·', (string) ($themeSettings['status_line'] ?? ''))), 1) as $bit)<span>{{ $bit }}</span>@if (! $loop->last)<span class="dot" aria-hidden="true"></span>@endif @endforeach</p>
        @endif
    </div>
</section>

{{-- ===== the sections ===== --}}
@foreach ($sections as $index => $nodes)
    @php
        $kicker = null;
        if ($nodes !== [] && $isKicker($nodes[0])) {
            $kicker = $nodes[0]->textContent;
            array_shift($nodes);
        }
        $hasFigure = collect($nodes)->contains(fn ($n) => $isFigure($n));
        $isLast = $index === array_key_last($sections);
        $tone = $index % 2 === 1 ? ' chrome' : '';
    @endphp

    @if ($variant === 'home' && $hasFigure)
        @php $flip = $index % 2 === 1; @endphp
        <section class="section{{ $tone }}" aria-label="{{ collect($nodes)->first(fn ($n) => $tag($n) === 'h2')?->textContent }}">
            <div class="wrap stop{{ $flip ? ' flip' : '' }}">
                <div class="stop-text">
                    @if ($kicker !== null)<span class="stop-num">{{ $kicker }}</span>@endif
                    @foreach ($nodes as $n)
                        @if ($tag($n) === 'h2')<h2 class="h3x">{!! $inner($n) !!}</h2>
                        @elseif (! $isFigure($n)){!! $html($n) !!}
                        @endif
                    @endforeach
                </div>
                <div>
                    @foreach ($nodes as $n)@if ($isFigure($n)){!! $html($n) !!}@endif @endforeach
                </div>
            </div>
        </section>
    @elseif ($variant === 'home')
        {{-- the closing: a heading, a line, buttons, links and the one line about moving --}}
        @php $links = []; $run = []; @endphp
        <section class="section closing"@if ($isLast) aria-label="{{ __('Closing') }}"@endif>
            <div class="wrap">
                @foreach ($nodes as $n)
                    @if ($tag($n) === 'h2'){!! $flush() !!}<h2>{!! $inner($n) !!}</h2>
                    @elseif ($isButton($n)) @php $run[] = $n; @endphp
                    @elseif ($isCta($n)) @php $links[] = $n; @endphp
                    @else
                        {!! $flush() !!}
                        <p class="{{ $n->getElementsByTagName('a')->length === 1 && str_contains($n->textContent, 'Moving') ? 'wp-line' : '' }}">{!! $inner($n) !!}</p>
                    @endif
                @endforeach
                {!! $flush() !!}
                @if ($links !== [])
                    <nav class="link-row" aria-label="{{ __('More about Teme') }}">
                        @foreach ($links as $l)@php $a = $l->getElementsByTagName('a')->item(0); @endphp<a href="{{ $a->getAttribute('href') }}">{{ $a->textContent }} <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>@endforeach
                    </nav>
                @endif
            </div>
        </section>
    @elseif ($variant === 'faq')
        <section class="sec tight"@if (collect($nodes)->contains(fn ($n) => $tag($n) === 'h3')) aria-label="{{ __('Questions') }}"@endif>
            <div class="wrap">
                @if (collect($nodes)->contains(fn ($n) => $tag($n) === 'h3'))
                    <div class="faq">
                        @php $open = false; @endphp
                        @foreach ($nodes as $n)
                            @if ($tag($n) === 'h3')
                                @if ($open)</div></details>@endif
                                <details><summary><h2>{!! $inner($n) !!}</h2></summary><div class="a">
                                @php $open = true; @endphp
                            @else
                                {!! $html($n) !!}
                            @endif
                        @endforeach
                        @if ($open)</div></details>@endif
                    </div>
                @else
                    <div class="after-faq muted">@foreach ($nodes as $n){!! $html($n) !!}@endforeach</div>
                @endif
            </div>
        </section>
    @elseif ($variant === 'roadmap')
        @if ($index === 0)<section class="sec tight" aria-label="{{ __('The three stages') }}"><div class="wrap"><div class="road-cols">@endif
        @if (collect($nodes)->contains(fn ($n) => $tag($n) === 'h2'))
            @php $col = $index === 0 ? 'ok' : ($index === 1 ? 'next' : 'later'); @endphp
            <section class="road-col{{ $col === 'later' ? ' later' : '' }}">
                @foreach ($nodes as $n)
                    @if ($tag($n) === 'h2')<h2><span class="dot-{{ $col }}" aria-hidden="true"></span>{!! $inner($n) !!}</h2>
                    @elseif ($tag($n) === 'ul')
                        <ul>
                            @foreach ($n->getElementsByTagName('li') as $li)
                                @php
                                    $kids = iterator_to_array($li->getElementsByTagName('p')->length ? $li->getElementsByTagName('p')->item(0)->childNodes : $li->childNodes);
                                    $b = $desc = $small = '';
                                    foreach ($kids as $k) {
                                        if ($k instanceof DOMElement && $k->tagName === 'strong' && $b === '') {
                                            $b = $k->textContent;
                                        } elseif ($k instanceof DOMElement && $k->tagName === 'em') {
                                            $small .= $k->textContent;
                                        } else {
                                            $desc .= $dom->saveHTML($k);
                                        }
                                    }
                                @endphp
                                <li><b>{{ $b }}</b><span>{!! trim($desc) !!}</span>@if ($small !== '')<small>{{ trim($small) }}</small>@endif</li>
                            @endforeach
                        </ul>
                    @else{!! $html($n) !!}
                    @endif
                @endforeach
            </section>
        @else
            @if ($index > 0)</div>@endif
            <p class="muted" style="margin-top:28px">@foreach ($nodes as $n){!! $inner($n) !!}@endforeach</p>
            @if ($index > 0)</div></section>@endif
        @endif
    @else
        {{-- landing --}}
        @php
            $head = [];
            $rest = [];
            $seenBody = false;
            foreach ($nodes as $n) {
                if (! $seenBody && ($tag($n) === 'h2' || $tag($n) === 'p')) {
                    $head[] = $n;
                } else {
                    $seenBody = true;
                    $rest[] = $n;
                }
            }
            $h3s = collect($rest)->filter(fn ($n) => $tag($n) === 'h3')->count();
            $isStop = $hasFigure && $h3s === 0;
            // Cards under no heading of their own sit directly under the page's h1: their titles are h2, so the order holds.
            $cardLevel = collect($head)->contains(fn ($n) => $tag($n) === 'h2') ? 3 : 2;
            $isClosing = $isLast && collect($rest)->contains(fn ($n) => $isButton($n) || $isCta($n));
        @endphp
        @if ($isStop)
            <section class="section{{ $tone }}">
                <div class="wrap stop{{ $index % 2 === 1 ? ' flip' : '' }}">
                    <div class="stop-text">
                        @if ($kicker !== null)<span class="stop-num">{{ $kicker }}</span>@endif
                        @foreach ($nodes as $n)
                            @if ($tag($n) === 'h2')<h2 class="h3x">{!! $inner($n) !!}</h2>@elseif (! $isFigure($n)){!! $html($n) !!}@endif
                        @endforeach
                    </div>
                    <div>@foreach ($nodes as $n)@if ($isFigure($n)){!! $html($n) !!}@endif @endforeach</div>
                </div>
            </section>
        @else
            <section class="{{ $isClosing ? 'section closing' : 'sec'.$tone }}">
                <div class="wrap">
                    @if ($head !== [])
                        <div class="sec-head">
                            @if ($kicker !== null)<span class="kicker">{{ $kicker }}</span>@endif
                            @foreach ($head as $n)
                                @if ($tag($n) === 'h2')<h2 id="{{ $slug($n->textContent) }}">{!! $inner($n) !!}</h2>@else<p class="sec-lead">{!! $inner($n) !!}</p>@endif
                            @endforeach
                        </div>
                    @endif
                    @php
                        $run = [];
                        $cards = [];
                        $card = null;
                        $out = [];
                        foreach ($rest as $n) {
                            if ($tag($n) === 'h3') {
                                $cards[] = ['h' => $n, 'body' => []];
                                $card = array_key_last($cards);
                            } elseif ($card !== null && ! $isFigure($n) && $tag($n) !== 'ol') {
                                $cards[$card]['body'][] = $n;
                            } else {
                                $card = null;
                                $out[] = ['block', $n];
                            }
                        }
                    @endphp
                    @if ($cards !== [])
                        <div class="cards cards-{{ count($cards) }}">
                            @foreach ($cards as $c)
                                <div class="card"><h{{ $cardLevel }}>{!! $inner($c['h']) !!}</h{{ $cardLevel }}>@foreach ($c['body'] as $bn){!! $html($bn) !!}@endforeach</div>
                            @endforeach
                        </div>
                    @endif
                    @foreach ($out as [, $n])
                        @if ($isButton($n) || $isCta($n))
                            @php $run[] = $n; @endphp
                        @else
                            {!! $flush() !!}
                            @if ($tag($n) === 'ol')<div class="steps">{!! $html($n) !!}</div>
                            @elseif ($isFigure($n))<div class="one-shot">{!! $html($n) !!}</div>
                            @elseif (str_contains($cls($n), 'card-callout'))<div class="banner" role="note">{!! $inner($n) !!}</div>
                            @else{!! $html($n) !!}
                            @endif
                        @endif
                    @endforeach
                    {!! $flush() !!}
                </div>
            </section>
        @endif
    @endif
@endforeach
