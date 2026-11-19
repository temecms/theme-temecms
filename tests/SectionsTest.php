<?php

declare(strict_types=1);

/*
 * How a landing page's body becomes the drawings' sections (partials/sectioned): the theme's own decisions from what
 * each section holds. The page is shaped as Teme hands an ArticleView to a template, with its body already HTML.
 */

use Teme\Testing\TemeTestCase;

uses(TemeTestCase::class);

beforeEach(function (): void {
    $this->theme(dirname(__DIR__))->activate();
});

/** The landing template's sections for a body of HTML. */
function sections(string $body): string
{
    $page = new class($body)
    {
        public string $title = 'A page';

        public ?string $standfirst = 'Its standfirst.';

        public ?array $breadcrumbs = null;

        public function __construct(public string $bodyHtml) {}

        public function field(string $handle): mixed
        {
            return null;
        }
    };

    return view('theme::partials.sectioned', ['page' => $page, 'variant' => 'landing'])->render();
}

it('draws a list whose items open with a bold word as a two-column table', function (): void {
    $html = sections('<p>Opening.</p><hr><h2>What a server needs</h2><ul><li><p><strong>Docker</strong> Docker Engine with Compose v2.</p></li><li><p><strong>A domain</strong> Its address.</p></li></ul>');

    expect($html)->toContain('<ul class="need-list"><li><b>Docker</b><span>Docker Engine with Compose v2.</span></li><li><b>A domain</b><span>Its address.</span></li></ul>');
});

it('draws a numbered list as step cards, and a picture after it full width rather than beside it', function (): void {
    $html = sections('<p>Opening.</p><hr><h2>Six steps</h2><ol><li><p><strong>Connect</strong> Give Teme the address.</p></li></ol><figure class="card-image"><img src="/a-w800.webp" alt="A step" width="800" height="400"></figure>');

    expect($html)->toContain('<ol class="mig-steps"><li><b>Connect</b><span>Give Teme the address.</span></li></ol>')
        ->toContain('<div class="one-shot"><figure class="card-image">')
        ->not->toContain('class="wrap stop');
});

it('joins a plain section to the one before as a wide card, and cards under no heading to its grid', function (): void {
    $html = sections('<p>Opening.</p><hr><p><strong>Where</strong></p><h2>Three guides.</h2><h3>Dokploy</h3><p>Run here.</p><p><em>Run on Dokploy, 8 Nov 2026</em></p><h3>Coolify</h3><p>Or here.</p><p><em>Not yet proved</em></p>'
        .'<hr><p><strong>In preparation</strong></p><h2>TemeCMS for Dokploy</h2><p>A template.</p><p><strong><a href="/docs/">Install by hand today</a></strong></p>'
        .'<hr><h2>Needs</h2><ul><li><p><strong>Docker</strong> Yes.</p></li></ul><hr><h3>Updates</h3><p>From the admin.</p><h3>Backups</h3><p>Every night.</p>');

    expect(substr_count($html, '<section class="sec'))->toBe(2)
        ->and($html)->toContain('<div class="card wide-card">')
        ->toContain('<h3>TemeCMS for Dokploy <span class="tag">In preparation</span></h3>')
        ->toContain('<span class="tag ok">Run on Dokploy, 8 Nov 2026</span>')
        ->toContain('<span class="tag">Not yet proved</span>')
        ->toContain('<h3>Updates</h3>')
        ->and(strpos($html, 'Backups'))->toBeGreaterThan(strpos($html, 'need-list'));
});

it('draws five cards as rows of one card, eight as checked tiles, and a guide card\'s icon from its title', function (): void {
    $card = fn (string $t) => '<h3>'.$t.'</h3><p>Words.</p>';
    $five = sections('<p>Opening.</p><hr><h2>Five</h2>'.implode('', array_map($card, ['One row per item', 'B', 'C', 'D', 'E'])));
    $eight = sections('<p>Opening.</p><hr><h2>Eight</h2>'.implode('', array_map($card, range('A', 'H'))));
    $three = sections('<p>Opening.</p><hr><h2>Three</h2>'.implode('', array_map($card, ['Dokploy', 'Coolify', 'A plain VPS'])));

    expect(substr_count($five, 'class="hood-row"'))->toBe(5)
        ->and($five)->toContain('<span class="ico" aria-hidden="true">')
        ->and(substr_count($eight, 'class="across-h"'))->toBe(8)
        ->and($three)->toContain('<div class="grid-3">')
        ->and(substr_count($three, 'class="ico"'))->toBe(3);
});

it('sets code beside its words, and the ten places as cards in place of their picture', function (): void {
    $code = sections('<p>Opening.</p><hr><h2>One package</h2><p>Lead.</p><p>A note.</p><figure class="card-snippet"><pre><code>enum A {}</code></pre><figcaption>A.php</figcaption></figure>');
    $places = sections('<p>Opening.</p><hr><p><strong>Where a plugin appears · Designed</strong></p><h2>Ten places</h2><figure class="card-image"><img src="/surfaces-w800.webp" alt="Ten" width="800" height="200"></figure><ul><li><p><strong>List cell</strong> A mark.</p></li><li><p><strong>Pane</strong> Work beside.</p></li></ul>');

    expect($code)->toContain('<div class="wrap two code-two">')
        ->toContain('<p class="small muted code-note">A note.</p>')
        ->toContain('<div class="code-side"><figure class="card-snippet">')
        ->and($places)->toContain('<ul class="surfaces"><li class="surf">')
        ->toContain('<span class="kicker">Where a plugin appears <span class="tag">Designed</span></span>')
        ->not->toContain('surfaces-w800.webp');
});

it('draws a line of facts split by middots in the opening as chips', function (): void {
    $html = sections('<p>PHP 8.4 · Laravel · PostgreSQL</p><hr><h2>A</h2><p>B.</p>');

    expect($html)->toContain('<p class="chips"><span class="tag">PHP 8.4</span><span class="tag">Laravel</span><span class="tag">PostgreSQL</span></p>');
});

it('gives a product picture the version drawn for a phone where the theme has one, and leaves another picture alone', function (): void {
    $html = sections('<figure class="card-image"><img src="/media/1/v1/editor-w1200.webp" alt="The editor" width="1200" height="1128"></figure><figure class="card-image"><img src="/media/2/v1/someone-w800.webp" alt="Someone" width="800" height="600"></figure><hr><h2>A</h2><p>B.</p>');

    expect($html)->toContain('<source media="(max-width: 640px)" srcset="'.asset('themes/temecms/shots/editor-phone.webp').'" width="686" height="2440">')
        ->toContain('<img src="/media/1/v1/editor-w1200.webp"')
        ->not->toContain('someone-phone');
});
