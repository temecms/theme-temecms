{{--
    A page drawn as an opening and sections. Tiptap content is a flat list of blocks, so the page's body (already HTML,
    drawn by core) is split at its horizontal rules: the first part is the opening, inside the hero under the title and
    the standfirst; every later part is a section. What a section holds decides its layout:
      - a paragraph that is only bold text is the small label above the section's heading;
      - a paragraph that is only a bold link is a quiet button; core's button cards are buttons;
      - a picture beside text is a "stop"; headings (h3) followed by paragraphs are cards; a numbered list is steps.
    A product picture gets its phone and dark versions (partials/pictures).
    Given $page (an ArticleView) and $variant: home, landing, faq or roadmap.
--}}
@include('theme::partials.pictures')
@php
    ob_start();
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
                    <div class="{{ $variant === 'home' ? 'o1-heroshot' : 'heroshot' }}">{!! temecms_pictures($html($node), wide: $variant !== 'home') !!}</div>
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
                @elseif ($variant === 'landing' && $tag($node) === 'p' && $node->getElementsByTagName('*')->length === 0 && count(explode(' · ', $node->textContent)) >= 3)
                    {{-- a line of short facts split by middots, such as the stack a page is built on, drawn as chips --}}
                    <p class="chips">@foreach (explode(' · ', trim($node->textContent)) as $chip)<span class="tag">{{ trim($chip) }}</span>@endforeach</p>
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
        {{-- landing: the sections as the drawings lay them out, from what each one holds --}}
        @php
            if (! isset($landing)) {
                $landing = ['skip' => [], 'toneN' => 0];
                // A section is "plain" when it is a heading with words and at most a button; "cards only" when it is cards under no heading.
                $sectionKind = function (array $ns) use ($tag, $isKicker, $isButton, $isCta): string {
                    if ($ns !== [] && $isKicker($ns[0])) {
                        array_shift($ns);
                    }
                    $tags = array_map($tag, $ns);
                    if (in_array('h2', $tags, true) && ! array_intersect($tags, ['h3', 'ul', 'ol', 'figure', 'div', 'table'])) {
                        return 'plain';
                    }
                    if (! in_array('h2', $tags, true) && ($tags[0] ?? '') === 'h3' && ! array_intersect($tags, ['ul', 'ol', 'figure', 'div', 'table'])
                        && ! collect($ns)->contains(fn ($n) => $isButton($n) || $isCta($n))) {
                        return 'cards';
                    }

                    return 'other';
                };
                $boldList = static function ($n) use ($tag): bool {
                    if ($tag($n) !== 'ul') {
                        return false;
                    }
                    $items = iterator_to_array($n->childNodes);
                    $items = array_values(array_filter($items, fn ($li) => $li instanceof DOMElement && $li->tagName === 'li'));
                    foreach ($items as $li) {
                        $holder = $li->getElementsByTagName('p')->item(0) ?? $li;
                        $first = $holder->firstChild;
                        if (! ($first instanceof DOMElement && $first->tagName === 'strong')) {
                            return false;
                        }
                    }

                    return $items !== [];
                };
                // A list item, or a numbered step, as its bold label and the words after it.
                $labelled = static function ($li) use ($dom): array {
                    $holder = $li->getElementsByTagName('p')->item(0) ?? $li;
                    $label = '';
                    $rest = '';
                    foreach (iterator_to_array($holder->childNodes) as $k) {
                        if ($label === '' && $k instanceof DOMElement && $k->tagName === 'strong') {
                            $label = trim($k->textContent);
                        } else {
                            $rest .= $dom->saveHTML($k);
                        }
                    }

                    return [$label, trim($rest)];
                };
                $isChip = static fn ($n): bool => $n instanceof DOMElement && $n->tagName === 'p' && $n->childNodes->length === 1 && $n->firstChild instanceof DOMElement && in_array($n->firstChild->tagName, ['em', 'i'], true);
                $glyph = static fn (string $d): string => '<span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" focusable="false">'.$d.'</svg></span>';
                // The drawings' icons, found by a word in a card's title; a title with none of these words gets no icon.
                $iconFor = static function (string $title) use ($glyph): string {
                    $icons = [
                        'dokploy' => '<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9Z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/>',
                        'package' => '<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9Z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/>',
                        'coolify' => '<path d="M7 18a5 5 0 0 1-.6-10A6 6 0 0 1 18 9a4.5 4.5 0 0 1-.5 9Z"/>',
                        'vps' => '<rect x="3" y="4" width="18" height="7" rx="2"/><rect x="3" y="13" width="18" height="7" rx="2"/><path d="M7 7.5h.01M7 16.5h.01"/>',
                        'api' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
                        'hook' => '<path d="M6 9a6 6 0 0 1 12 0c0 6 3 8 3 8H3s3-2 3-8"/><path d="M10 21h4"/>',
                        'row' => '<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v14c0 1.7 3.6 3 8 3s8-1.3 8-3V5"/><path d="M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/>',
                        'structured' => '<path d="M8 4H7a2 2 0 0 0-2 2v4a2 2 0 0 1-2 2 2 2 0 0 1 2 2v4a2 2 0 0 0 2 2h1M16 4h1a2 2 0 0 1 2 2v4a2 2 0 0 0 2 2 2 2 0 0 0-2 2v4a2 2 0 0 1-2 2h-1"/>',
                        'address' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
                        'path' => '<path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6Z"/><path d="m9 12 2 2 4-4"/>',
                        'read-only' => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>',
                    ];
                    $words = strtolower($title);
                    foreach ($icons as $word => $d) {
                        if (preg_match('/\b'.preg_quote($word, '/').'s?\b/', $words)) {
                            return $glyph($d);
                        }
                    }

                    return '';
                };
                $check = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>';
                // The ten places a plugin appears, drawn small as the drawing does; a label it doesn't know gets an empty frame.
                $surfaceDrawings = [
                    'list cell' => '<i style="left:10px;right:10px;top:14px;height:8px"></i><i style="left:10px;right:40px;top:32px;height:8px"></i><i class="a" style="right:10px;top:30px;width:22px;height:12px;border-radius:6px"></i><i style="left:10px;right:10px;top:52px;height:8px"></i>',
                    'row action' => '<i style="left:10px;right:10px;top:14px;height:8px"></i><i class="w" style="right:10px;top:26px;width:70px;height:40px"></i><i class="s" style="right:16px;top:36px;width:54px;height:6px"></i><i style="right:16px;top:50px;width:40px;height:6px"></i>',
                    'rail section' => '<i style="left:10px;width:58%;top:12px;bottom:12px;background:var(--teme-surface)"></i><i class="s" style="right:10px;width:28%;top:14px;height:10px"></i><i style="right:10px;width:28%;top:30px;height:6px"></i><i style="right:10px;width:28%;top:42px;height:6px"></i>',
                    'pane' => '<i style="left:10px;width:58%;top:12px;bottom:12px;background:var(--teme-surface)"></i><i class="a" style="right:10px;width:28%;top:12px;bottom:12px;opacity:.85"></i>',
                    'dialog' => '<i style="inset:0;background:var(--teme-scrim);border-radius:0"></i><i class="w" style="left:25%;right:25%;top:14px;bottom:14px"></i><i class="a" style="right:30%;bottom:20px;width:24px;height:8px"></i>',
                    'workspace' => '<i class="w" style="inset:8px"></i><i class="s" style="left:16px;right:16px;top:16px;height:8px"></i><i style="left:16px;width:40%;top:32px;bottom:16px"></i><i style="right:16px;width:40%;top:32px;bottom:16px"></i>',
                    'page' => '<i style="left:8px;width:18%;top:8px;bottom:8px"></i><i class="s" style="left:30%;right:10px;top:14px;height:8px"></i><i style="left:30%;right:10px;top:30px;height:6px"></i><i style="left:30%;right:30%;top:42px;height:6px"></i>',
                    'dashboard panel' => '<i class="w" style="left:10px;width:42%;top:12px;bottom:12px"></i><i class="w" style="right:10px;width:42%;top:12px;bottom:12px"></i><i class="a" style="left:18px;width:30px;top:24px;height:14px"></i>',
                    'status row' => '<i class="w" style="right:10px;top:10px;width:66%;height:54px"></i><i style="right:18px;top:20px;width:8px;height:8px;border-radius:50%;background:var(--teme-ok)"></i><i style="right:34px;top:21px;width:46%;height:6px"></i><i style="right:18px;top:38px;width:8px;height:8px;border-radius:50%;background:var(--teme-warn)"></i><i style="right:34px;top:39px;width:40%;height:6px"></i>',
                    'command' => '<i class="w" style="left:14%;right:14%;top:10px;height:20px"></i><i class="s" style="left:14%;right:14%;top:36px;height:10px"></i><i style="left:14%;right:30%;top:50px;height:6px"></i>',
                ];
                // A list as its items' bold labels and words: step cards, the ten places with their small drawings, or a two-column table.
                $drawList = function ($list, string $element, string $class) use ($labelled, $surfaceDrawings): string {
                    $out = '<'.$element.' class="'.$class.'">';
                    foreach ($list->childNodes as $li) {
                        if (! ($li instanceof DOMElement && $li->tagName === 'li')) {
                            continue;
                        }
                        [$label, $words] = $labelled($li);
                        $drawing = $class === 'surfaces' ? '<div class="sf" aria-hidden="true">'.($surfaceDrawings[strtolower($label)] ?? '').'</div>' : '';
                        $out .= '<li'.($class === 'surfaces' ? ' class="surf"' : '').'>'.$drawing.'<b>'.e($label).'</b><span>'.$words.'</span></li>';
                    }

                    return $out.'</'.$element.'>';
                };
                // A card: its icon, its title (with a one-word chip such as Soon beside it), its words, then its other chips and its link at the foot.
                $drawCard = function (array $c, int $level, string $class = 'card') use ($inner, $html, $isChip, $isCta, $isButton, $iconFor): string {
                    $title = trim($c['h']->textContent);
                    $beside = '';
                    $foot = '';
                    $words = '';
                    foreach ($c['body'] as $bn) {
                        if ($isChip($bn)) {
                            $chip = trim($bn->textContent);
                            if (! str_contains($chip, ' ')) {
                                $beside .= ' <span class="tag">'.e($chip).'</span>';
                            } else {
                                $foot .= '<span class="tag'.(preg_match('/^(not|no)\b/i', $chip) ? '' : ' ok').'">'.e($chip).'</span>';
                            }
                        } elseif ($isCta($bn) || $isButton($bn)) {
                            $a = $isButton($bn) ? $bn : $bn->getElementsByTagName('a')->item(0);
                            $foot .= '<a class="go" href="'.e($a->getAttribute('href')).'">'.e(trim($a->textContent)).'</a>';
                        } else {
                            $words .= $html($bn);
                        }
                    }

                    return '<div class="'.$class.'">'.$iconFor($title).'<h'.$level.'>'.$inner($c['h']).$beside.'</h'.$level.'>'.$words.($foot !== '' ? '<span class="foot">'.$foot.'</span>' : '').'</div>';
                };
                $gather = function (array $ns) use ($tag, $isFigure): array {
                    $cards = [];
                    $card = null;
                    $out = [];
                    foreach ($ns as $n) {
                        if ($tag($n) === 'h3') {
                            $cards[] = ['h' => $n, 'body' => []];
                            $card = array_key_last($cards);
                        } elseif ($card !== null && ! $isFigure($n) && ! in_array($tag($n), ['ol', 'ul', 'h2'], true)) {
                            $cards[$card]['body'][] = $n;
                        } else {
                            $card = null;
                            $out[] = $n;
                        }
                    }

                    return [$cards, $out];
                };
                // Cards drawn by how many there are: rows in one card for five, small checked tiles for six or more, else a grid.
                $drawCards = function (array $cards, int $level) use ($drawCard, $inner, $html, $iconFor, $check, $isChip): string {
                    $count = count($cards);
                    if ($count >= 6) {
                        $out = '<div class="across-grid">';
                        foreach ($cards as $c) {
                            $out .= '<div><h'.$level.' class="across-h">'.$check.$inner($c['h']).'</h'.$level.'>';
                            foreach ($c['body'] as $bn) {
                                $out .= '<span>'.$inner($bn).'</span>';
                            }
                            $out .= '</div>';
                        }

                        return $out.'</div>';
                    }
                    if ($count === 5) {
                        $out = '<div class="hood">';
                        foreach ($cards as $c) {
                            $out .= '<div class="hood-row">'.$iconFor(trim($c['h']->textContent)).'<h'.$level.'>'.$inner($c['h']).'</h'.$level.'>';
                            foreach ($c['body'] as $bn) {
                                $out .= $html($bn);
                            }
                            $out .= '</div>';
                        }

                        return $out.'</div>';
                    }
                    $out = '<div class="grid-'.max(2, min(3, $count)).'">';
                    foreach ($cards as $c) {
                        $out .= $drawCard($c, $level);
                    }

                    return $out.'</div>';
                };
            }
            if (in_array($index, $landing['skip'], true)) {
                continue;
            }

            $head = [];
            $rest = [];
            $seenBody = false;
            foreach ($nodes as $n) {
                if (! $seenBody && ($tag($n) === 'h2' || ($tag($n) === 'p' && ! $isCta($n) && ! $isChip($n)))) {
                    $head[] = $n;
                } else {
                    $seenBody = true;
                    $rest[] = $n;
                }
            }
            [$cards, $out] = $gather($rest);
            $hasHead = collect($head)->contains(fn ($n) => $tag($n) === 'h2');
            $snippet = collect($rest)->first(fn ($n) => $tag($n) === 'figure' && str_contains($cls($n), 'card-snippet'));
            $grid = collect($rest)->first(fn ($n) => $boldList($n) && $hasFigure);
            // Cards under no heading of their own sit directly under the page's h1: their titles are h2, so the order holds.
            $cardLevel = $hasHead ? 3 : 2;
            $isClosing = $isLast && $cards === [] && collect($rest)->contains(fn ($n) => $isButton($n) || $isCta($n));
            // The sections after this one that join it: a plain heading with words becomes a wide card, cards under no heading join its grid.
            $joined = [];
            for ($next = $index + 1; $next < count($sections) && $hasHead && ! $isClosing; $next++) {
                $kind = $sectionKind($sections[$next]);
                if ($kind === 'other' || ($kind === 'plain' && $next === array_key_last($sections))) {
                    break;
                }
                $joined[] = [$kind, $sections[$next]];
                $landing['skip'][] = $next;
            }
            $tight = ! $hasHead && ! $isClosing;
            $tone = '';
            if (! $tight && ! $isClosing) {
                $tone = $landing['toneN'] % 2 === 1 ? ' chrome' : '';
                $landing['toneN']++;
            }
            $kickerTags = [];
            if ($kicker !== null && str_contains($kicker, ' · ')) {
                $kickerParts = array_map('trim', explode(' · ', $kicker));
                $kicker = array_shift($kickerParts);
                $kickerTags = $kickerParts;
            }
        @endphp
        <section class="{{ $isClosing ? 'section closing' : 'sec'.$tone.($tight ? ' tight' : '') }}"@if (! $hasHead && ! $isClosing) aria-label="{{ trim(collect($cards)->map(fn ($c) => trim($c['h']->textContent))->implode(', ')) }}"@endif>
            @if ($snippet !== null && $cards === [])
                {{-- words on the left, the code on the right --}}
                <div class="wrap two code-two">
                    <div>
                        @if ($kicker !== null)<span class="kicker">{{ $kicker }}</span>@endif
                        @foreach ($head as $i => $n)
                            @if ($tag($n) === 'h2')<h2 id="{{ $slug($n->textContent) }}" class="h2">{!! $inner($n) !!}</h2>
                            @elseif ($i === 1)<p class="sec-lead">{!! $inner($n) !!}</p>
                            @else<p class="small muted code-note">{!! $inner($n) !!}</p>
                            @endif
                        @endforeach
                        @foreach ($out as $n)@if ($n !== $snippet){!! $html($n) !!}@endif @endforeach
                    </div>
                    <div class="code-side">{!! $html($snippet) !!}</div>
                </div>
            @else
                <div class="wrap">
                    @if ($head !== [])
                        <div class="sec-head">
                            @if ($kicker !== null)<span class="kicker">{{ $kicker }}@foreach ($kickerTags as $kt) <span class="tag">{{ $kt }}</span>@endforeach</span>@endif
                            @foreach ($head as $n)
                                @if ($tag($n) === 'h2')<h2 id="{{ $slug($n->textContent) }}">{!! $inner($n) !!}</h2>
                                @else<p class="sec-lead">{!! $inner($n) !!}</p>
                                @endif
                            @endforeach
                        </div>
                    @endif
                    @if ($cards !== [])
                        {!! $drawCards($cards, $cardLevel) !!}
                    @endif
                    @php $run = []; @endphp
                    @foreach ($out as $n)
                        @if ($isButton($n) || $isCta($n))
                            @php $run[] = $n; @endphp
                        @else
                            {!! $flush() !!}
                            @if ($tag($n) === 'ol')
                                {!! $drawList($n, 'ol', 'mig-steps') !!}
                            @elseif ($grid !== null && $n === $grid)
                                {!! $drawList($n, 'ul', 'surfaces') !!}
                            @elseif ($grid !== null && $isFigure($n))
                                {{-- the picture of the ten places is drawn above as cards --}}
                            @elseif ($boldList($n))
                                {!! $drawList($n, 'ul', 'need-list') !!}
                            @elseif ($isFigure($n))<div class="one-shot">{!! $html($n) !!}</div>
                            @elseif (str_contains($cls($n), 'card-callout'))<div class="banner" role="note">{!! $inner($n) !!}</div>
                            @else{!! $html($n) !!}
                            @endif
                        @endif
                    @endforeach
                    {!! $flush() !!}
                    @foreach ($joined as [$kind, $joinedNodes])
                        @php
                            $joinedKicker = null;
                            if ($joinedNodes !== [] && $isKicker($joinedNodes[0])) {
                                $joinedKicker = trim($joinedNodes[0]->textContent);
                                array_shift($joinedNodes);
                            }
                        @endphp
                        @if ($kind === 'cards')
                            @php [$joinedCards] = $gather($joinedNodes); @endphp
                            {!! $drawCards($joinedCards, 3) !!}
                        @else
                            @php $joinedButtons = array_values(array_filter($joinedNodes, fn ($n) => $isButton($n) || $isCta($n))); @endphp
                            <div class="card wide-card">
                                <div class="wide-words">
                                    @foreach ($joinedNodes as $n)
                                        @if ($tag($n) === 'h2')<h3>{!! $inner($n) !!}@if ($joinedKicker !== null) <span class="tag">{{ $joinedKicker }}</span>@endif</h3>
                                        @elseif (! $isButton($n) && ! $isCta($n)){!! $html($n) !!}
                                        @endif
                                    @endforeach
                                </div>
                                @foreach ($joinedButtons as $b){!! $asButton($b) !!}@endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </section>
    @endif
@endforeach
{!! temecms_pictures((string) ob_get_clean()) !!}
