<?php

declare(strict_types=1);

/*
 * The theme's tests, written as the handbook's testing page says: the kit's test case, the theme through
 * theme(), content through publish(), and the site's own answers through fetch(). Run them from a Teme install:
 *
 *   php artisan test /path/to/theme-temecms/tests
 *
 * The kit makes empty items, so what these check is what a theme alone decides: which template answers, the
 * one h1, the footer's words and the follow link from the theme's settings. What the site's pages look
 * like (sections, pictures, axe at 1280 and 375) is checked by `node tests/pages.mjs` against a site loaded
 * with the temecms-site plugin's starting content.
 */

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
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

    expect($html)->toContain('A content management system for publications, made by Krafty Sprouts Media. Teme is Ijaw for <em>create</em>.')
        ->and($html)->not->toContain('mailto:')
        ->and($html)->not->toMatch('/<form[^>]*method="post"/i')
        ->and($html)->not->toMatch('/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[a-z]{2,}/');
});

it('leaves the status line out while it is empty, as it is by default', function (): void {
    $html = $this->fetch('/')->getContent();

    expect($html)->not->toContain('class="foot-status"')
        ->and($html)->toMatch('/<div class="foot-base">\s*<span class="foot-licence">MIT licence<\/span>/');
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

/** The documentation's version label, drawn from these values alone, as the theme's settings and Teme give them. */
function docsVersion(array $data): string
{
    return Blade::render((string) file_get_contents(dirname(__DIR__).'/views/partials/docs-version.blade.php'), $data);
}

it('labels the documentation with the version setting when it is set', function (): void {
    $html = docsVersion(['themeSettings' => ['docs_version' => '1.1'], 'teme' => ['version' => '1.2.0', 'release' => '1.2.0']]);

    expect($html)->toContain('<b>1.1</b>');
});

it('labels the documentation with the running release when the setting is empty', function (): void {
    $html = docsVersion(['themeSettings' => ['docs_version' => ''], 'teme' => ['version' => '1.2.0', 'release' => '1.2.0']]);

    expect($html)->toContain('<b>1.2.0</b>');
});

it('shows no version label when the setting is empty and Teme gives no release', function (): void {
    $html = docsVersion(['themeSettings' => ['docs_version' => '']]);

    expect($html)->not->toContain('class="ver"');
});

/** One of the footer's partials, drawn from these values alone. */
function footPart(string $name, array $data): string
{
    return Blade::render((string) file_get_contents(dirname(__DIR__).'/views/partials/'.$name.'.blade.php'), $data);
}

it('draws the status line at the foot\'s start and the licence at its end, as the drawing does', function (): void {
    expect(footPart('foot-base', ['status' => 'Stable · Teme 1.2', 'licence' => 'MIT licence']))
        ->toMatch('/<div class="foot-base">\s*<span class="foot-status">Stable · Teme 1.2<\/span>\s*<span class="foot-licence">MIT licence<\/span>/');
});

it('sets the footer\'s words between asterisks in italics, and draws the tagline and maker line when its line is empty', function (): void {
    $words = fn (string $line) => footPart('foot-words', ['line' => $line, 'tagline' => 'A content management system for publications.', 'maker' => 'Made by Krafty Sprouts Media.']);

    expect($words('Made by us. Teme is Ijaw for *create*.'))->toContain('<p>Made by us. Teme is Ijaw for <em>create</em>.</p>')
        ->and($words(''))->toContain('<p>A content management system for publications. Made by Krafty Sprouts Media.</p>')
        ->and($words('<b>*x*</b>'))->toContain('&lt;b&gt;<em>x</em>&lt;/b&gt;');
});

it('lists the more links and the follow button in the phone menu, whose icon turns into a cross', function (): void {
    $html = $this->fetch('/')->getContent();

    expect($html)->toMatch('#<ul class="menu-more">\s*<li><a href="/faq/">FAQ</a></li><li><a href="/roadmap/">Roadmap</a></li>\s*</ul>#')
        ->toContain('class="menu-close"')
        ->toMatch('/<div class="mobile-nav">.*class="btn btn-primary"/s');
});

it('links the theme\'s own icon, in its light and dark tones, with a PNG for browsers without SVG icons', function (): void {
    $html = $this->fetch('/')->getContent();
    $svg = (string) file_get_contents(dirname(__DIR__).'/public/icon.svg');

    expect($html)->toContain('<link rel="icon" href="'.asset('themes/temecms/icon.svg').'" type="image/svg+xml">')
        ->toContain('<link rel="icon" href="'.asset('themes/temecms/icon-32.png').'" type="image/png" sizes="32x32">')
        ->and($svg)->toContain('@media (prefers-color-scheme:dark)')
        ->and(is_file(dirname(__DIR__).'/public/icon-32.png'))->toBeTrue();
});
