<?php

declare(strict_types=1);

/*
 * The theme's tests, written as the handbook's testing page says: the kit's test case, the theme through
 * theme(), content through publish(), and the site's own answers through fetch(). Run them from a Teme install:
 *
 *   php artisan test /path/to/theme-temecms/tests
 *
 * The kit makes empty items, so what these check is what a theme alone decides: which template answers, the
 * one h1, the footer's words and the follow link from the theme's settings. What the seeded site's pages look
 * like (sections, pictures, axe at 1280 and 375) is checked by `node tests/pages.mjs` against a seeded site.
 */

use Illuminate\Support\Facades\Artisan;
use Teme\Testing\TemeTestCase;

uses(TemeTestCase::class);

/** The one h1 of an answer, and how many there were. */
function h1sOf(string $html): int
{
    return preg_match_all('/<h1[\s>]/i', $html);
}

/** The template `teme:theme:resolve` says answers an address. */
function templateFor(string $path): string
{
    Artisan::call('teme:theme:resolve', ['url' => $path]);
    preg_match('/theme::templates\.[a-z0-9-]+/', Artisan::output(), $found);

    return $found[0] ?? '';
}

beforeEach(function (): void {
    $this->theme(dirname(__DIR__))->activate();
});

it('passes the theme check', function (): void {
    expect($this->theme(dirname(__DIR__))->check())->toBe([]);
});

it('answers a post with its single template and one h1', function (): void {
    $post = $this->publish('A post for the blog', 'post');
    $response = $this->fetch($post->url);

    $response->assertOk();
    expect(h1sOf($response->getContent()))->toBe(1)
        ->and(templateFor($post->url))->toBe('theme::templates.single');
});

it('answers a page with its page template and one h1', function (): void {
    $page = $this->publish('A page', 'page');
    $response = $this->fetch($page->url);

    $response->assertOk();
    expect(h1sOf($response->getContent()))->toBe(1)
        ->and(templateFor($page->url))->toBe('theme::templates.single-page');
});

it('draws the blog list at the site address when nothing else is the home page', function (): void {
    $this->publish('First post', 'post');
    $response = $this->fetch('/');

    $response->assertOk();
    expect(h1sOf($response->getContent()))->toBe(1)
        ->and(templateFor('/'))->toBe('theme::templates.latest');
    $response->assertSee('First post');
});

it('answers an address with nothing at it as not found, with one h1', function (): void {
    $response = $this->fetch('/nothing-is-here/');

    $response->assertNotFound();
    expect(h1sOf($response->getContent()))->toBe(1)
        ->and(templateFor('/nothing-is-here/'))->toBe('theme::templates.notfound');
});

it('says who makes the site in one plain line, and names no person, address or form', function (): void {
    $html = $this->fetch('/')->getContent();

    expect($html)->toContain('Made by Krafty Sprouts Media.')
        ->and($html)->not->toContain('mailto:')
        ->and($html)->not->toMatch('/<form[^>]*method="post"/i')
        ->and($html)->not->toMatch('/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[a-z]{2,}/');
});

it('leaves the status line out while it is empty, as it is by default', function (): void {
    $html = $this->fetch('/')->getContent();

    expect($html)->not->toMatch('/<div class="foot-base">\s*<span><\/span>/')
        ->and($html)->toMatch('/<div class="foot-base">\s*<span>MIT licence<\/span>/');
});

it('draws the follow link from its setting, and only to an https address', function (): void {
    $html = $this->fetch('/')->getContent();

    expect($html)->toContain('href="https://github.com/temecms"')
        ->and($html)->toContain('Follow the build');
});

it('draws the theme\'s own icon in the header and the footer', function (): void {
    $html = $this->fetch('/')->getContent();

    expect(substr_count($html, 'class="ic-deep"'))->toBeGreaterThanOrEqual(4);
});

it('draws the header and footer menus as labelled landmarks', function (): void {
    $html = $this->fetch('/')->getContent();

    // A slot with no menu bound is core's own loud note outside production (data-teme-unbound): not a landmark of the theme's.
    preg_match_all('/<nav(?![^>]*(?:aria-label|data-teme-unbound))[^>]*>/i', $html, $unlabelled);

    expect($unlabelled[0])->toBe([]);
});
