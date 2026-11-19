<?php

declare(strict_types=1);

/*
 * The directory, Submit, Showcase and the docs, drawn from the temecms-site plugin's fields. The kit publishes empty
 * items, so these draw the theme's views with items shaped as Teme hands them to a template (a field() that answers a
 * FieldView's value, text and emptiness), and check what the theme decides: which facts show, how lists are read from
 * their fields, and what the docs do to a guide's body.
 */

use Teme\Testing\TemeTestCase;

uses(TemeTestCase::class);

/** A field as a template sees it: its value, its words and whether it is empty. */
function fieldView(mixed $value, ?string $text = null, ?string $url = null): object
{
    $empty = $value === null || $value === '' || $value === false;

    return (object) ['value' => $value, 'text' => $text ?? (is_scalar($value) ? (string) $value : ''), 'empty' => $empty, 'url' => $url, 'image' => null];
}

/** A listing or an article, with these fields by handle; any other handle is one its type doesn't have. */
function directoryItem(string $title, array $fields = [], array $extra = []): object
{
    return new class($title, $fields, $extra)
    {
        public string $url;

        public ?string $standfirst;

        public mixed $kicker;

        public mixed $image = null;

        public string $type = 'plugin';

        public string $bodyHtml = '';

        public function __construct(public string $title, private array $fields, array $extra)
        {
            $this->url = $extra['url'] ?? '/plugins/'.strtolower(str_replace(' ', '-', $title)).'/';
            $this->standfirst = $extra['standfirst'] ?? null;
            $this->kicker = $extra['kicker'] ?? null;
            foreach ($extra as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $value;
                }
            }
        }

        public function field(string $handle): ?object
        {
            return array_key_exists($handle, $this->fields) ? fieldView($this->fields[$handle]) : null;
        }
    };
}

/** Teme SEO as the site plugin's sync would leave it. */
function temeSeo(array $more = []): object
{
    return directoryItem('Teme SEO', $more + [
        'package' => 'temecms/seo',
        'summary' => 'An audit of every post.',
        'author_name' => 'TemeCMS',
        'author_github' => 'temecms',
        'latest_version' => '1.0.1',
        'works_with' => '1.1, 1.2',
        'updated_on' => new DateTimeImmutable('2026-11-17'),
        'listed_on' => new DateTimeImmutable('2026-09-02'),
        'stars' => 214,
        'open_issues' => 3,
        'picked' => true,
        'categories' => 'seo, content',
        'icon' => '',
        'icon_image' => null,
        'link_source' => 'https://github.com/temecms/seo',
        'link_issues' => 'https://github.com/temecms/seo/issues',
        'link_docs' => '',
        'link_support' => '',
        'releases' => json_encode([
            ['version' => '1.0.1', 'date' => '2026-11-17', 'works_with' => ['1.1', '1.2'], 'held' => false],
            ['version' => '1.0.0', 'date' => '2026-11-03', 'works_with' => ['1.1'], 'held' => false],
            ['version' => '1.1.0', 'date' => '2026-11-18', 'works_with' => ['1.2'], 'held' => true],
        ]),
        'capabilities' => json_encode([['key' => 'content.read', 'words' => 'Read your posts and pages']]),
        'screenshots' => json_encode([]),
        'changelog' => "# Changelog\n\n## 1.0.1 - 2026-11-17\n\n### Fixed\n\n- The Audit reads stored problems.\n",
    ], ['kicker' => (object) ['name' => 'Search engines', 'url' => '/directory_category/seo/', 'taxonomy' => 'directory_category']]);
}

function drawView(string $view, array $data = []): string
{
    // The site and the theme's settings come from Teme's own composers: the settings' defaults are the manifest's.
    return view('theme::'.$view, $data)->render();
}

beforeEach(function (): void {
    $this->theme(dirname(__DIR__))->activate();
    view('theme::partials.directory-data')->render();
});

it('reads a listing from the site plugin\'s fields, leaving held releases out', function (): void {
    $listing = temecms_listing(temeSeo());

    expect($listing['summary'])->toBe('An audit of every post.')
        ->and($listing['works'])->toBe(['1.2', '1.1'])
        ->and(array_column($listing['releases'], 'version'))->toBe(['1.0.1', '1.0.0'])
        ->and($listing['categories'])->toBe(['seo', 'content'])
        ->and($listing['categoryLabels'])->toBe(['seo' => 'SEO', 'content' => 'Content'])
        ->and($listing['capabilities'])->toBe([['key' => 'content.read', 'words' => 'Read your posts and pages']])
        ->and(temecms_works_range($listing))->toBe('Teme 1.1 to 1.2');
});

it('words a category the directory knows its own way, and another by the term the site wrote', function (): void {
    $shop = directoryItem('Till', ['categories' => 'Shop, seo'], ['kicker' => (object) ['name' => 'Shop and sales', 'url' => '/directory-category/shop/', 'taxonomy' => 'directory_category']]);

    expect(temecms_listing($shop)['categoryLabels'])->toBe(['shop' => 'Shop and sales', 'seo' => 'SEO']);
});

it('takes the excerpt as the summary when the summary field is empty', function (): void {
    $listing = temecms_listing(temeSeo(['summary' => '']));

    expect($listing['summary'])->toBe('')
        ->and(temecms_listing(directoryItem('Bare', ['summary' => ''], ['standfirst' => 'From the excerpt.']))['summary'])->toBe('From the excerpt.');
});

it('reads an unreadable list field as empty rather than failing', function (): void {
    $listing = temecms_listing(temeSeo(['releases' => '{not json', 'screenshots' => '[{"caption":"no media"}]']));

    expect($listing['releases'])->toBe([])->and($listing['screenshots'])->toBe([]);
});

it('draws a plugin card as one link with the same facts as every card', function (): void {
    $html = drawView('partials.dir.card', ['listing' => temecms_listing(temeSeo())]);

    expect($html)->toContain('href="/plugins/teme-seo/"')
        ->toContain('<h3>Teme SEO</h3>')
        ->toContain('by TemeCMS')
        ->toContain('v1.0.1')
        ->toContain('Teme 1.2')
        ->toContain('datetime="2026-11-17"')
        ->toContain('data-cats="seo content"')
        ->toContain('data-works="1.2 1.1"')
        ->and(substr_count($html, '<a '))->toBe(1);
});

it('counts the browse page\'s categories and offers the Teme versions newest first', function (): void {
    $page = (object) [
        'items' => [temeSeo(), directoryItem('Formwell', ['categories' => 'forms', 'works_with' => '1.0, 1.1', 'author_name' => 'Ardent Works'])],
        'pagination' => (object) ['previous' => null, 'next' => null],
        'subject' => null,
    ];
    $html = drawView('partials.dir.browse', ['kind' => 'plugins', 'page' => $page]);

    expect($html)->toMatch('/data-cat="seo"[^>]*><span>SEO<\/span><span class="n">1<\/span>/')
        ->toMatch('/data-cat="social"[^>]*><span>Social<\/span><span class="n">0<\/span>/')
        ->toMatch('/data-cat="all"[^>]*><span>All plugins<\/span><span class="n">2<\/span>/')
        ->toMatch('/<option value="1.2">1.2 \(latest\)<\/option><option value="1.1">1.1<\/option><option value="1.0">1.0<\/option>/')
        ->toContain('Picked by the Teme team')
        ->toContain('2 plugins')
        ->toContain('No plugin matches.');
});

it('leaves out the strips that have nothing to show', function (): void {
    $page = (object) ['items' => [directoryItem('Bare', [])], 'pagination' => (object) ['previous' => null, 'next' => null], 'subject' => null];
    $html = drawView('partials.dir.browse', ['kind' => 'plugins', 'page' => $page]);

    expect($html)->not->toContain('Picked by the Teme team')->not->toContain('Recently updated</h2>');
});

it('draws a plugin\'s facts with ratings kept for later and a way to report the listing', function (): void {
    $html = drawView('partials.dir.facts', ['listing' => temecms_listing(temeSeo()), 'isTheme' => false, 'catBase' => '/plugins/']);

    expect($html)->toContain('Reviewed by Teme')
        ->toContain('1.0.1 approved on 17 Nov 2026.')
        ->toContain('2 releases')
        ->toContain('Teme 1.1 to 1.2')
        ->toContain('<code>temecms/seo</code>')
        ->toContain('href="/plugins/#category=seo">SEO</a>')
        ->toContain('Ratings and reviews: later')
        ->toContain('https://github.com/temecms/directory/issues/new?title=Report%3A%20temecms%2Fseo')
        ->toContain('Report this listing');
});

it('draws the install steps with the Composer command for the package', function (): void {
    $html = drawView('partials.dir.install', ['listing' => temecms_listing(temeSeo()), 'isTheme' => false]);

    expect($html)->toContain('composer require temecms/seo')->toContain('php artisan teme:plugins:enable temecms/seo');
});

it('draws the changelog from Markdown without its own title, and every listed release', function (): void {
    $html = drawView('partials.dir.changelog', ['listing' => temecms_listing(temeSeo())]);

    expect($html)->not->toContain('>Changelog<')
        ->toContain('<h3>1.0.1 - 2026-11-17</h3>')
        ->toContain('The Audit reads stored problems.')
        ->toContain('All 2 versions, with their Teme ranges');
});

it('gives a guide\'s code its language and the callouts their word, wraps tables, and lists the h2s', function (): void {
    $prose = temecms_doc_prose('<h2>Get the recipe</h2><pre><code class="language-sh">docker compose up</code></pre><div class="card-callout card-callout--tip" role="note"><p>Wait for it.</p></div><table><tr><td>a</td></tr></table><h2>Get the recipe</h2>');

    expect($prose['html'])->toContain('<div class="codeblock"><div class="cb-bar"><span class="lang">sh</span></div><pre tabindex="0">')
        ->toContain('<div class="callout tip" role="note">')
        ->toContain('<b>Tip</b><p>Wait for it.</p>')
        ->toContain('<div class="table-wrap" tabindex="0" role="region" aria-label="Table"><table>')
        ->and($prose['toc'])->toBe([['id' => 'get-the-recipe', 'text' => 'Get the recipe'], ['id' => 'get-the-recipe-2', 'text' => 'Get the recipe']]);
});

it('builds the docs tree from the docs menu\'s headings, or else from the three guides', function (): void {
    $link = fn (string $label, bool $current = false) => ['label' => $label, 'url' => '/docs/'.strtolower($label).'/', 'current' => $current, 'opensNewTab' => false, 'heading' => false, 'children' => []];
    $fromDocs = temecms_docs_tree(['docs' => [['label' => 'Getting started', 'url' => null, 'current' => false, 'opensNewTab' => false, 'heading' => true, 'children' => [$link('Introduction'), $link('Installing', true)]]]], []);
    $fromGuides = temecms_docs_tree(['docs-operators' => [$link('Installing')]], ['docs-editors' => 'Editors', 'docs-operators' => 'Operators']);

    expect($fromDocs)->toHaveCount(1)
        ->and($fromDocs[0]['label'])->toBe('Getting started')
        ->and($fromDocs[0]['current'])->toBeTrue()
        ->and(array_column(temecms_docs_flat($fromDocs), 'label'))->toBe(['Introduction', 'Installing'])
        ->and(array_column($fromGuides, 'label'))->toBe(['Operators']);
});

it('makes each group of a guide\'s menu a heading of the tree, as the drawing does', function (): void {
    $link = fn (string $label, bool $current = false) => ['label' => $label, 'url' => '/docs/'.strtolower($label).'/', 'current' => $current, 'opensNewTab' => false, 'heading' => false, 'children' => []];
    $group = fn (string $label, array $children) => ['label' => $label, 'url' => null, 'current' => false, 'opensNewTab' => false, 'heading' => false, 'children' => $children];
    $tree = temecms_docs_tree(['docs-editors' => [$group('Getting started', [$link('Introduction', true)]), $group('Writing', [$link('Posts')])]], ['docs-editors' => 'Editors']);

    expect(array_column($tree, 'label'))->toBe(['Getting started', 'Writing'])
        ->and($tree[0]['current'])->toBeTrue()
        ->and(array_column(temecms_docs_flat($tree), 'label'))->toBe(['Introduction', 'Posts']);
});

it('marks the docs version an address belongs to, and shows the banner only off the latest', function (): void {
    $latest = temecms_docs_versions('1.2=/docs/, 1.1=/docs/1.1/, Next=/docs/next/', '/docs/install/');
    $older = temecms_docs_versions('1.2=/docs/, 1.1=/docs/1.1/, Next=/docs/next/', '/docs/1.1/install/');

    expect(array_column($latest, 'current'))->toBe([true, false, false])
        ->and(array_column($older, 'current'))->toBe([false, true, false])
        ->and(drawView('partials.dir.docs-banner', ['versions' => $latest]))->not->toContain('ver-banner')
        ->and(drawView('partials.dir.docs-banner', ['versions' => $older]))->toContain('You are reading the documentation for 1.1.');
});

it('offers a page template for Submit, and draws the form to the site plugin\'s address with its honeypot', function (): void {
    $page = $this->publish('Submit a plugin or theme', 'page');
    $html = drawView('templates.single-page-submit', ['page' => directoryItem('Submit a plugin or theme', [], ['standfirst' => 'Anyone can submit.'])]);

    expect(json_decode((string) file_get_contents(dirname(__DIR__).'/teme-theme.json'), true)['templates']['page'])->toHaveKey('submit')
        ->and($html)->toContain('action="/temecms-site/submit-package"')
        ->toContain('name="repository"')->toContain('name="kind"')->toContain('name="category"')->toContain('name="contact"')->toContain('name="agree"')
        ->toContain('name="website"')
        ->toContain('What happens next')
        ->toContain('<option value="developer">Developer tools</option>')
        ->and(substr_count($html, '<h1'))->toBe(1);
});

it('links a showcase site\'s theme and plugins and counts its kinds', function (): void {
    $site = new class
    {
        public string $title = 'temecms.com';

        public mixed $image = null;

        public DateTimeImmutable $published;

        public function __construct()
        {
            $this->published = new DateTimeImmutable('2026-11-01');
        }

        public function field(string $handle): ?object
        {
            return match ($handle) {
                'site_kind' => fieldView('documentation', 'Documentation'),
                'site_address' => fieldView('https://temecms.com'),
                'theme_item' => fieldView('id', 'Teme Default', '/theme-directory/teme-default/'),
                'plugin_item_1' => fieldView('id', 'Teme SEO', '/plugins/teme-seo/'),
                'owner_line' => fieldView('Teme’s own site.'),
                'screenshot' => fieldView(null),
                default => str_starts_with($handle, 'plugin_item_') ? fieldView(null) : null,
            };
        }
    };
    $page = (object) ['items' => [$site], 'pagination' => (object) ['previous' => null, 'next' => null], 'subject' => null];
    $html = drawView('templates.archive-showcase', ['page' => $page]);

    expect($html)->toContain('<a href="/theme-directory/teme-default/">Teme Default</a>')
        ->toContain('<a href="/plugins/teme-seo/">Teme SEO</a>')
        ->toMatch('/data-kind="documentation"[^>]*>Documentation <span class="n num">1<\/span>/')
        ->toMatch('/data-kind="news"[^>]*>News <span class="n num">0<\/span>/')
        ->toContain('action="/temecms-site/submit-site"')
        ->toContain('>temecms.com</a>');
});
