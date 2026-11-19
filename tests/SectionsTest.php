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

it('gives a product picture the version drawn for a phone where the theme has one, and leaves another picture alone', function (): void {
    $html = sections('<figure class="card-image"><img src="/media/1/v1/editor-w1200.webp" alt="The editor" width="1200" height="1128"></figure><figure class="card-image"><img src="/media/2/v1/someone-w800.webp" alt="Someone" width="800" height="600"></figure><hr><h2>A</h2><p>B.</p>');

    expect($html)->toContain('<source media="(max-width: 640px)" srcset="'.asset('themes/temecms/shots/editor-phone.webp').'" width="686" height="2440">')
        ->toContain('<img src="/media/1/v1/editor-w1200.webp"')
        ->not->toContain('someone-phone');
});
