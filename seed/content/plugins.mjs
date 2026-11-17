import { a, b, c, p, h2, h3, kicker, ul, hr, callout, snippet, image } from '../lib.mjs';

const CAP = { 'content.read': 'Read posts and pages', 'media.read': 'Read the Media library', 'http': 'Reach outside services, only the hosts it names' };
const CAP_SHORT = { 'content.read': 'Reads posts and pages', 'media.read': 'Reads the Media library', 'http': 'Reaches the hosts it names' };
const HOOK = { ContentSaved: 'An item is saved', ContentPublished: 'An item is published', ContentUnpublished: 'An item is taken off the site', MediaStored: 'A file is added to the library' };
const CATS = { workflow: 'Workflow', search: 'Search and social', reading: 'Reading' };

const PLUGINS = [
    { id: 'review-notifier', name: 'Review notifier', cat: 'workflow', icon: 'send', line: 'Tells editors when a draft is waiting for review.',
      lead: 'Tells editors when a draft is waiting for review, in their inbox and by email if they choose.',
      caps: ['content.read'], hooks: ['ContentSaved'],
      where: [['Your inbox', 'A notice when a draft waits for you.'], ['Account › Notifications', 'Each person turns the email on or off.'], ['Settings › Plugin settings', 'Who is told, by role.']], short: 'Inbox and settings' },
    { id: 'search-pings', name: 'Search pings', cat: 'search', icon: 'search', line: 'Tells search engines when a page is published or changed.',
      lead: 'Tells search engines when a page is published, changed or taken off the site, so new stories are found sooner.',
      caps: ['content.read', 'http'], hooks: ['ContentPublished', 'ContentUnpublished'], hosts: 'The search engines it tells',
      where: [['Settings › Plugin settings', 'Which search engines to tell.'], ['Status', 'A banner when pings keep failing.']], short: 'Settings and Status' },
    { id: 'related-posts', name: 'Related posts', cat: 'reading', icon: 'layers', line: 'Chooses the related stories shown after each article.',
      lead: 'Chooses the related stories shown after each article, where the theme shows them.',
      caps: ['content.read'], hooks: ['ContentPublished'],
      where: [['After each article', 'In the place the theme gives related stories.'], ['Settings › Plugin settings', 'How many, and from which categories.']], short: 'Public pages and settings' },
    { id: 'social-posting', name: 'Social posting', cat: 'search', icon: 'share', line: "Shares a new story to the site's social accounts.",
      lead: "Shares a new story to the site's social accounts when it is published, with its title, picture and link.",
      caps: ['content.read', 'media.read', 'http'], hooks: ['ContentPublished'], hosts: 'The social networks it posts to',
      where: [['The editor', 'A section beside the text: where this story will be shared.'], ['Settings › Plugin settings', 'The accounts to post to.'], ['Status', 'A banner when an account stops answering.']], short: 'Editor, settings and Status' },
    { id: 'pre-publish-checklist', name: 'Pre-publish checklist', cat: 'workflow', icon: 'list2', line: 'Checks an article against a list you set before it goes out.',
      lead: 'Checks an article against a list you set, such as a picture with alt text or a category chosen, before it goes out.',
      caps: ['content.read'], hooks: ['ContentSaved'],
      where: [['The editor', 'The checklist beside the text, ticked as you write.'], ['Settings › Plugin settings', 'Your list.']], short: 'Editor and settings' },
    { id: 'structured-data-packs', name: 'Structured-data packs', cat: 'search', icon: 'braces', line: 'Recipe, Event, Product snippet and Review markup for search engines.',
      lead: "Adds Recipe, Event, Product snippet and Review markup to the pages that need it, for search engines' rich results.",
      caps: ['content.read'], hooks: ['ContentSaved'],
      where: [['The editor', 'Fields for a recipe, an event, a product or a review.'], ['Public pages', "The markup in the page's structured data."]], short: 'Editor and public pages' },
];

const lc = (s) => s.charAt(0).toLowerCase() + s.slice(1);

export default PLUGINS.map((pl) => {
    const pkg = 'teme/' + pl.id;
    const count = pl.caps.length === 1 ? '1 permission' : pl.caps.length + ' permissions';
    const hasHttp = pl.caps.includes('http');
    return {
        key: pl.id,
        type: 'plugin',
        title: pl.name,
        slug: pl.id,
        excerpt: pl.line,
        fields: {
            category: pl.cat,
            package: pkg,
            icon: pl.icon,
            asks: pl.caps.map((k) => CAP_SHORT[k]).join(' · '),
            where_short: pl.short,
            status: 'Planned',
        },
        body: {
            type: 'doc', content: [
                callout('note', "Example. This plugin is planned, not built. The page shows how a plugin's page will look; its package name, settings and screens are drawn for the example."),
                p(pl.lead),
                ul(
                    [b('Status'), ' Planned'],
                    [b('Made by'), ' The Teme team'],
                    [b('For'), ' ' + CATS[pl.cat]],
                    [b('Asks for'), ' ' + count],
                    [b('Latest version'), ' None yet'],
                    [b('Package'), ' ', c(pkg), ' (example)'],
                ),
                hr(),

                kicker('01 · Permission'),
                h2('What it asks for'),
                p('An administrator sees this before the plugin runs. An update that asks for more waits for a new yes.'),
                ul(
                    ...pl.caps.map((k) => [b(CAP[k]), ' (', c(k), ')' + (k === 'http' && pl.hosts ? ': ' + lc(pl.hosts) : '')]),
                    ...pl.hooks.map((k) => [b('Listens: ' + lc(HOOK[k])), ' (', c(k), ')']),
                ),
                image('consent', { alt: `The enable dialog for the ${pl.name} plugin, drawn for the example: it lists what the plugin asks to do (${pl.caps.map((k) => CAP[k].toLowerCase()).join('; ')}) and what it listens for, with buttons to agree or cancel.`, caption: 'The enable dialog, drawn from Site › Plugins.' }),
                hr(),

                kicker('02 · In the admin'),
                h2('Where it appears'),
                ul(...pl.where.map((w) => [b(w[0] + '.'), ' ' + w[1]])),
                p('Settings and Status banners are built today; sections in the editor are designed and arrive with the next releases of the plugin API.'),
                image('plugin-row', { alt: `The ${pl.name} plugin in the Plugins page of the admin, drawn for the example once it is enabled: its name, its switch and the links it added.`, caption: 'In Site › Plugins, enabled. Drawn for the example.' }),
                hr(),

                kicker('03 · Safety'),
                h2('What it keeps and sends'),
                h3('What it keeps'),
                p("Its settings, with the site's own. A plugin's manifest lists any table it adds, so removing it removes exactly that."),
                h3('What leaves the site'),
                p(hasHttp
                    ? 'Only requests to ' + lc(pl.hosts) + ', through Teme’s door: https, to the hosts an administrator granted, nowhere else.'
                    : 'Nothing. It asks for no outside access, so Teme gives it none.'),
                h3('Report a problem'),
                p('Open an issue on GitHub, when Teme is public.'),
                hr(),

                kicker('04 · Get it'),
                h2('Install, versions, support'),
                h3('Install'),
                p("The server's operator adds the package; an administrator enables it in Site › Plugins, or from the console. Installing from the admin is planned."),
                snippet('bash', `composer require ${pkg}\nphp artisan teme:plugins:enable ${pkg}`),
                h3('Versions'),
                p('No release yet. Each release will list the Teme versions it works with, from its manifest, and what changed. A listed release never changes.'),
                h3('Support'),
                p('Made by the Teme team. Licence set by the plugin; not decided for this one.'),
                p(a('Source on GitHub', 'https://github.com/temecms')),
            ],
        },
    };
});
