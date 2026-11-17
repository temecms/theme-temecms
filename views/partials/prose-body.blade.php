{{-- A body's HTML as the site draws it, given $html: headings get anchors, and a code block that scrolls can be reached by keyboard. --}}
@php
    libxml_use_internal_errors(true);
    $proseDom = new DOMDocument('1.0', 'UTF-8');
    $proseDom->loadHTML('<?xml encoding="utf-8" ?><body>'.$html.'</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    foreach (['h2', 'h3'] as $level) {
        foreach ($proseDom->getElementsByTagName($level) as $h) {
            if (! $h->hasAttribute('id')) {
                $h->setAttribute('id', trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($h->textContent)), '-'));
            }
        }
    }
    foreach ($proseDom->getElementsByTagName('pre') as $pre) {
        $pre->setAttribute('tabindex', '0');
    }
    $proseOut = '';
    foreach ($proseDom->getElementsByTagName('body')->item(0)->childNodes as $proseNode) {
        $proseOut .= $proseDom->saveHTML($proseNode);
    }
@endphp
{!! $proseOut !!}
