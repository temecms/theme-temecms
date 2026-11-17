<?php

declare(strict_types=1);

/**
 * Seeds a Teme install with the temecms.com site's content through Teme's own Operations (never raw SQL,
 * never HTML): types and their fields, the product pictures, pages, posts, guides, plugins, themes,
 * the theme's regions, its menus and the site's settings. Run from the Teme install's folder:
 *
 *   php /path/to/theme-temecms/seed/seed.php types fields media content regions menus settings
 *
 * SEED_ADMIN_ID names the user (an administrator) the content is made as; it defaults to 1.
 *
 * Steps are repeatable: what exists (by slug, or by the state file) is reused, never duplicated.
 * `activate` makes the theme the site's theme and the front page its home page: run it last, and only
 * when no other session is using the site.
 */

use App\Domain\Contents\Models\Content;
use App\Domain\Contents\Operations\CreateDraft;
use App\Domain\Contents\Operations\Input\CreateDraftInput;
use App\Domain\Contents\Operations\Input\PublishRevisionInput;
use App\Domain\Contents\Operations\Input\SaveDraftInput;
use App\Domain\Contents\Operations\Input\UpdateContentDetailsInput;
use App\Domain\Contents\Operations\PublishRevision;
use App\Domain\Contents\Operations\SaveDraft;
use App\Domain\Contents\Operations\UpdateContentDetails;
use App\Domain\Design\Operations\ActivateTheme;
use App\Domain\Design\Operations\Input\ActivateThemeInput;
use App\Domain\Fields\Operations\AddField;
use App\Domain\Fields\Operations\Input\AddFieldInput;
use App\Domain\Fields\Registry\FieldRegistry as FR;
use App\Domain\Fields\Stored\FieldDefinition;
use App\Domain\Media\Operations\Input\UpdateMediaDetailsInput;
use App\Domain\Media\Operations\Input\UploadMediaInput;
use App\Domain\Media\Operations\UpdateMediaDetails;
use App\Domain\Media\Operations\UploadMedia;
use App\Domain\Menus\Operations\BindNavigationSlot;
use App\Domain\Menus\Operations\CreateNavigation;
use App\Domain\Menus\Operations\Input\BindNavigationSlotInput;
use App\Domain\Menus\Operations\Input\CreateNavigationInput;
use App\Domain\Menus\Operations\Input\SaveNavigationItemsInput;
use App\Domain\Menus\Operations\SaveNavigationItems;
use App\Domain\Regions\Operations\CreateRegion;
use App\Domain\Regions\Operations\Input\CreateRegionInput;
use App\Domain\Settings\Operations\Input\UpdateSettingsInput;
use App\Domain\Settings\Operations\UpdateSettings;
use App\Domain\Themes\TemplateChoice;
use Illuminate\Support\Facades\Artisan;
use Teme\Testing\Acting;
use Teme\Testing\Person;

$themeRoot = dirname(__DIR__);
$teme = getcwd();
require $teme.'/vendor/autoload.php';
$app = require $teme.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$steps = array_slice($argv, 1);
$content = json_decode((string) file_get_contents(__DIR__.'/content.json'), true, 512, JSON_THROW_ON_ERROR);
$statePath = __DIR__.'/.state.json';
$state = is_file($statePath) ? json_decode((string) file_get_contents($statePath), true) : [];
$state += ['media' => [], 'content' => [], 'regions' => []];
$save = static function () use (&$state, $statePath): void {
    file_put_contents($statePath, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
};

$adminId = (int) (getenv('SEED_ADMIN_ID') ?: 1); // the site's administrator, who the content is made as
$admin = \App\Models\User::query()->findOrFail($adminId);
$actor = new Person($admin->id, $admin->name, $admin->email, 'administrator');
$as = static fn (Closure $step, string $what): mixed => (new Acting)->as($actor, $step, $what);
$say = static function (string $line): void {
    fwrite(STDOUT, $line."\n");
};
$aid = (string) $adminId;

// ---------------------------------------------------------------- types
if (in_array('types', $steps, true)) {
    $file = sys_get_temp_dir().'/temecms-types.json';
    file_put_contents($file, json_encode([
        'format' => 'teme-types', 'version' => 1, 'fields' => [], 'taxonomies' => [], 'grants' => [],
        'types' => [
            ['key' => 'doc', 'singular' => 'Guide', 'plural' => 'Documentation', 'segment' => 'doc-pages', 'icon' => 'book-open', 'public' => true, 'archive' => true, 'searchable' => true, 'supports' => ['excerpt', 'seo'], 'pattern' => '/docs/{slug}/'],
            ['key' => 'plugin', 'singular' => 'Plugin', 'plural' => 'Plugins', 'segment' => 'plugin-pages', 'icon' => 'plug', 'public' => true, 'archive' => true, 'searchable' => true, 'supports' => ['excerpt', 'seo'], 'pattern' => '/plugins/{slug}/'],
            ['key' => 'theme', 'singular' => 'Theme', 'plural' => 'Themes', 'segment' => 'theme-pages', 'icon' => 'palette', 'public' => true, 'archive' => true, 'searchable' => true, 'supports' => ['excerpt', 'seo', 'featured_image'], 'pattern' => '/theme-directory/{slug}/'],
        ],
    ], JSON_PRETTY_PRINT));
    Artisan::call('teme:types:import', ['file' => $file]);
    $say(trim(Artisan::output()));
}

// ---------------------------------------------------------------- fields
$fieldList = [
    'page' => [
        ['kicker', 'Kicker', 'text', []],
        ['emphasis', 'Words to set in italics in the title', 'text', []],
    ],
    'doc' => [
        ['guide', 'Guide', 'select', ['options' => ['editors' => "The editors' guide", 'operators' => "The operators' guide", 'developers' => 'For developers']]],
        ['group', 'Group', 'text', []],
        ['proof', 'Proof level', 'select', ['options' => ['' => 'None', 'ci' => 'Checked by CI', 'tests' => 'Checked by tests', 'dokploy' => 'Run on Dokploy', 'none' => 'Not yet proved']]],
        ['order', 'Order in its group', 'number', []],
        ['soon', 'Not written yet', 'boolean', []],
        ['written_for', 'Written for', 'text', []],
    ],
    'plugin' => [
        ['category', 'Category', 'select', ['options' => ['workflow' => 'Workflow', 'search' => 'Search and social', 'reading' => 'Reading']]],
        ['package', 'Package', 'text', []],
        ['icon', 'Icon', 'text', []],
        ['asks', 'What it asks for, in a line', 'text', []],
        ['where_short', 'Where it appears, in a line', 'text', []],
        ['status', 'Status', 'text', []],
    ],
    'theme' => [
        ['badge', 'Badge', 'text', []],
        ['meta', 'Facts for the card', 'text', []],
        ['status', 'Status', 'text', []],
    ],
];
if (in_array('fields', $steps, true)) {
    $reg = app(\App\Domain\Fields\FieldRegistry::class);
    foreach ($fieldList as $type => $defs) {
        $have = array_map(static fn ($f) => $f->handle, $reg->forType($type));
        foreach ($defs as [$handle, $label, $fieldType, $settings]) {
            if (in_array($handle, $have, true)) {
                continue;
            }
            if (isset($settings['options'])) { $settings = ['choices' => array_map(static fn ($v, $l) => ['value' => (string) $v, 'label' => $l], array_keys(array_filter($settings['options'], static fn ($l, $v) => $v !== '', ARRAY_FILTER_USE_BOTH)), array_values(array_filter($settings['options'], static fn ($l, $v) => $v !== '', ARRAY_FILTER_USE_BOTH)))]; }
            $def = FieldDefinition::fromArray(['type' => $type, 'handle' => $handle, 'label' => $label, 'fieldType' => $fieldType, 'settings' => $settings, 'show' => 'hidden']);
            $as(fn () => app(AddField::class)->execute(new AddFieldInput($type, $def, $aid)), "Adding $type.$handle");
            $say("field $type.$handle added");
        }
    }
}

// ---------------------------------------------------------------- media
if (in_array('media', $steps, true)) {
    foreach ($content['media'] as $key => $m) {
        if (isset($state['media'][$key])) {
            continue;
        }
        $path = __DIR__.'/media/'.$key.'.png';
        $item = $as(fn () => app(UploadMedia::class)->execute(new UploadMediaInput($path, $key.'.png', $aid, allowDuplicate: true)), "Uploading $key");
        $id = is_array($item) ? $item['id'] : $item->id;
        $as(fn () => app(UpdateMediaDetails::class)->execute(new UpdateMediaDetailsInput($id, $aid, $m['title'], $m['alt'], null, null)), "Describing $key");
        $state['media'][$key] = $id;
        $save();
        $say("media $key -> $id");
    }
}

// ---------------------------------------------------------------- content
$fieldKeys = static function (string $type): array {
    $map = [];
    foreach (app(\App\Domain\Fields\FieldRegistry::class)->forType($type) as $f) {
        $map[$f->handle] = $f->key;
    }

    return $map;
};
$resolve = static function (mixed $node) use (&$resolve, &$state): mixed {
    if (is_array($node)) {
        foreach ($node as $k => $v) {
            $node[$k] = $resolve($v);
            if ($k === 'contentId' && is_string($node[$k]) && str_starts_with($node[$k], 'teme:content/')) {
                $node[$k] = substr($node[$k], strlen('teme:content/'));
            }
        }

        return $node;
    }
    if (is_string($node)) {
        if (str_starts_with($node, 'teme:seed/')) {
            $ref = substr($node, strlen('teme:seed/'));
            [$kind, $key] = explode(':', $ref, 2);
            $id = $state['content'][$kind.':'.$key] ?? null;
            if ($id === null) {
                throw new RuntimeException("The seed names [$ref], which has not been made.");
            }

            return 'teme:content/'.$id;
        }
        if (str_starts_with($node, 'seed:')) {
            $key = substr($node, 5);

            return $state['media'][$key] ?? throw new RuntimeException("No picture [$key].");
        }
    }

    return $node;
};
if (in_array('content', $steps, true)) {
    $items = [];
    foreach (['pages', 'posts', 'docs', 'plugins', 'themes'] as $bucket) {
        foreach ($content[$bucket] ?? [] as $it) {
            $items[] = $it;
        }
    }
    // Pass 1: every item exists as a draft with its slug, so links between items can name them.
    foreach ($items as $it) {
        $sid = $it['type'].':'.$it['key'];
        if (isset($state['content'][$sid])) {
            continue;
        }
        $keys = $fieldKeys($it['type']);
        $fields = [];
        foreach (($it['fields'] ?? []) as $h => $v) {
            $fields[$keys[$h] ?? throw new RuntimeException("No field {$it['type']}.$h")] = $v;
        }
        if (($it['excerpt'] ?? '') !== '') {
            $fields[$keys['excerpt']] = $it['excerpt'];
        }
        if (isset($it['template'])) {
            $fields[$keys['template']] = $it['template'] === 'default' ? '' : $it['template'];
        }
        /** @var Content|null $c */
        $c = Content::query()->where('type', $it['type'])->where('title', $it['title'])->first();
        $fields = $resolve($fields);
        $c ??= $as(fn () => app(CreateDraft::class)->execute(new CreateDraftInput($it['type'], $it['title'], ['type' => 'doc', 'content' => [['type' => 'paragraph']]], $fields, $aid)), "Drafting {$it['title']}");
        $as(fn () => app(UpdateContentDetails::class)->execute(new UpdateContentDetailsInput($c->id, (string) $c->working_revision_id, $aid, slug: $it['slug'])), "Addressing {$it['title']}");
        $state['content'][$sid] = $c->id;
        $save();
        $say("draft $sid -> {$c->id}");
    }
    // Pass 2: the body, with its links resolved to IDs, saved and published.
    foreach ($items as $it) {
        $sid = $it['type'].':'.$it['key'];
        $c = Content::query()->findOrFail($state['content'][$sid]);
        if (! empty($c->published_revision_id) && ! in_array($it['type'], array_filter(explode(',', (string) getenv('SEED_FORCE'))), true)) {
            continue;
        }
        $keys = $fieldKeys($it['type']);
        $fields = [];
        foreach (($it['fields'] ?? []) as $h => $v) {
            $fields[$keys[$h]] = $v;
        }
        if (($it['excerpt'] ?? '') !== '') {
            $fields[$keys['excerpt']] = $it['excerpt'];
        }
        if (isset($it['template'])) {
            $fields[$keys['template']] = $it['template'] === 'default' ? '' : $it['template'];
        }
        $fields = $resolve($fields);
        $body = $resolve($it['body']);
        $as(fn () => app(SaveDraft::class)->execute(new SaveDraftInput($c->id, $it['title'], $body, $fields, (string) $c->working_revision_id, $aid)), "Writing {$it['title']}");
        $c->refresh();
        $as(fn () => app(PublishRevision::class)->execute(new PublishRevisionInput($c->id, (string) $c->working_revision_id, $aid)), "Publishing {$it['title']}");
        if (isset($it['publishedAt'])) {
            $c->refresh();
            $as(fn () => app(UpdateContentDetails::class)->execute(new UpdateContentDetailsInput($c->id, (string) $c->working_revision_id, $aid, publishedAt: $it['publishedAt'])), "Dating {$it['title']}");
        }
        $say("published $sid");
    }
}

// ---------------------------------------------------------------- regions
if (in_array('regions', $steps, true)) {
    foreach ($content['regions'] as $name => $body) {
        $slug = 'temecms--'.$name;
        $c = Content::query()->where('type', 'region')->where('slug', $slug)->first();
        if ($c === null) {
            $c = $as(fn () => app(CreateRegion::class)->execute(new CreateRegionInput('temecms', $name, $aid)), "Making region $name");
        }
        $body = $resolve($body);
        $as(fn () => app(SaveDraft::class)->execute(new SaveDraftInput($c->id, $c->title, $body, [], (string) $c->working_revision_id, $aid)), "Writing region $name");
        $c->refresh();
        $as(fn () => app(PublishRevision::class)->execute(new PublishRevisionInput($c->id, (string) $c->working_revision_id, $aid)), "Publishing region $name");
        $say("region $name published");
    }
}

// ---------------------------------------------------------------- menus
if (in_array('menus', $steps, true)) {
    $slotFor = ['primary' => 'TemeCMS: Header', 'footer-teme' => 'TemeCMS: Footer, Teme', 'footer-build' => 'TemeCMS: Footer, Build', 'footer-project' => 'TemeCMS: Footer, Project'];
    $page = static fn (string $key): string => $state['content']['page:'.$key] ?? throw new RuntimeException("No page $key");
    $trees = [
        'primary' => [['url', '/docs/', 'Docs'], ['url', '/plugins/', 'Plugins'], ['url', '/theme-directory/', 'Themes'], ['page', 'hosting', 'Hosting'], ['page', 'developers', 'Developers']],
        'footer-teme' => [['home', null, 'Home'], ['page', 'migration', 'Moving from WordPress'], ['page', 'hosting', 'Hosting'], ['page', 'roadmap', 'Roadmap']],
        'footer-build' => [['url', '/docs/', 'Docs'], ['url', '/plugins/', 'Plugins'], ['url', '/theme-directory/', 'Themes'], ['page', 'developers', 'Developers']],
        'footer-project' => [['url', 'https://github.com/temecms', 'GitHub'], ['url', 'https://github.com/temecms', 'Releases'], ['page', 'faq', 'FAQ'], ['page', 'blog', 'Blog']],
    ];
    foreach ($slotFor as $slot => $name) {
        $nav = \App\Domain\Menus\Models\Navigation::query()->where('name', $name)->first()
            ?? $as(fn () => app(CreateNavigation::class)->execute(new CreateNavigationInput($name, $aid)), "Making menu $name");
        $tree = [];
        foreach ($trees[$slot] as [$kind, $target, $label]) {
            $tree[] = match ($kind) {
                'url' => ['kind' => 'url', 'url' => $target, 'label' => $label, 'children' => []],
                'page' => ['kind' => 'content', 'target_id' => $page($target), 'label' => $label, 'children' => []],
                'home' => ['kind' => 'home', 'label' => $label, 'children' => []],
            };
        }
        $as(fn () => app(SaveNavigationItems::class)->execute(new SaveNavigationItemsInput($nav->id, $tree, $aid)), "Filling menu $name");
        $as(fn () => app(BindNavigationSlot::class)->execute(new BindNavigationSlotInput('temecms', $slot, $nav->id, $aid)), "Placing menu $name");
        $say("menu $slot placed");
    }

    // The documentation's trees: one menu per guide, a heading per group, the guides under it (the docs-sync plugin will keep these).
    $guideNames = ['editors' => "TemeCMS: Docs, the editors' guide", 'operators' => "TemeCMS: Docs, the operators' guide", 'developers' => 'TemeCMS: Docs, for developers'];
    foreach ($guideNames as $guide => $name) {
        $nav = \App\Domain\Menus\Models\Navigation::query()->where('name', $name)->first()
            ?? $as(fn () => app(CreateNavigation::class)->execute(new CreateNavigationInput($name, $aid)), "Making menu $name");
        $groups = [];
        foreach ($content['docs'] as $d) {
            if (($d['fields']['guide'] ?? '') !== $guide) {
                continue;
            }
            $groups[$d['fields']['group']][] = ['kind' => 'content', 'target_id' => $state['content']['doc:'.$d['key']], 'label' => $d['title'], 'children' => []];
        }
        $tree = [];
        foreach ($groups as $label => $children) {
            $tree[] = ['kind' => 'heading', 'label' => $label, 'children' => $children];
        }
        $as(fn () => app(SaveNavigationItems::class)->execute(new SaveNavigationItemsInput($nav->id, $tree, $aid)), "Filling menu $name");
        $as(fn () => app(BindNavigationSlot::class)->execute(new BindNavigationSlotInput('temecms', 'docs-'.$guide, $nav->id, $aid)), "Placing menu $name");
        $say("menu docs-$guide placed");
    }
}

// ---------------------------------------------------------------- settings
$updateGroup = static function (string $group, array $values) use ($as, $aid): void {
    $version = (int) (\App\Domain\Settings\Models\SettingsGroupRecord::query()->where('group_key', $group)->value('version') ?? 0);
    $as(fn () => app(UpdateSettings::class)->execute(new UpdateSettingsInput($group, $values, [], [], $version, $aid)), "Settings $group");
};
if (in_array('settings', $steps, true)) {
    $updateGroup('general', ['site_title' => 'Teme', 'tagline' => 'A content management system for publications. Teme is Ijaw for create.']);
    $say('site title and tagline set');
}

// ---------------------------------------------------------------- theme (before content: a page picks a template only from the active theme)
if (in_array('theme', $steps, true)) {
    $as(fn () => app(ActivateTheme::class)->execute(new ActivateThemeInput('temecms', $aid)), 'Activating temecms');
    $say('temecms is the site theme');
}

// ---------------------------------------------------------------- activate (last, and only when the site is ours to switch)
if (in_array('activate', $steps, true)) {
    $page = static fn (string $key): string => $state['content']['page:'.$key] ?? throw new RuntimeException("No page $key");
    $as(fn () => app(\App\Domain\Addresses\Operations\ChangeAddresses::class)->execute(new \App\Domain\Addresses\Operations\Input\ChangeAddressesInput([], [], null, 'page', $page('home'), $page('blog'), $aid)), 'Choosing the home page');
    $say('the home page is the front page, the blog page the posts page');
}

// ---------------------------------------------------------------- addresses: the theme directory is not at /themes/, where core serves theme assets from
if (in_array('addresses', $steps, true)) {
    $as(fn () => app(\App\Domain\Addresses\Operations\ChangeAddresses::class)->execute(new \App\Domain\Addresses\Operations\Input\ChangeAddressesInput(['theme' => '/theme-directory/{slug}/'], [], null, null, null, null, $aid, ['theme' => 'theme-directory'])), 'Moving the theme directory');
    $say('the theme directory is at /theme-directory/');
}
