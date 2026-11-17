import { a, b, c, p, h2, h3, kicker, ul, hr, snippet, image } from '../lib.mjs';

const lead1 = 'A reading-first theme for a publication: a lead story, a clean feed, and articles set for long reading.';
const lead2 = 'Built only against the published theme contract, to prove that a theme needs nothing else.';

export default [
    {
        key: 'teme-default',
        type: 'theme',
        title: 'Teme Default',
        slug: 'teme-default',
        excerpt: lead1,
        fields: { badge: 'Ships with Teme', meta: 'Version 0.1.0 · Teme team · Light and dark · MIT', status: 'Ships with Teme' },
        body: {
            type: 'doc', content: [
                p(lead1),
                ul(
                    [b('Status'), ' Ships with Teme'],
                    [b('Made by'), ' The Teme team'],
                    [b('Version'), ' 0.1.0'],
                    [b('Type'), ' Newsreader and Instrument Sans'],
                    [b('Licence'), ' MIT'],
                ),
                image('site-desktop', { alt: "Teme Default's front page on a wide screen, in an invented newsroom: a lead story with its picture, a row of pinned stories and a feed of stories below.", width: 'wide' }),
                image('site-phone', { alt: "Teme Default's front page on a phone: the header with its menu button, the lead story and the first stories of the feed in a single column." }),
                p("Drawn from the theme's own colours and type, light and dark following the reader. The newsroom is invented."),
                hr(),

                kicker('What it supports'),
                h2("Everything a publication's pages need."),
                h3('Menus and a region'),
                p('Two menus, Header and Footer; on small screens the header menu opens without JavaScript. One region after the article, for up to five callout or button cards.'),
                h3('Thirteen settings'),
                p('Header layout, colour scheme, the lead story, list or grid, featured images, author box, related posts and more, previewed on Settings › Design before they are published.'),
                h3('Readable by everyone'),
                p('One heading per page, labelled menus, visible focus, and rules for reduced motion and forced colours, checked by tests on every template.'),
                p('Its templates: latest posts, articles, pages (default and full width), archives, search, comments and not found. Nothing needs JavaScript to read.'),
                hr(),

                kicker('Get it'),
                h2('Install, versions, source'),
                h3('Install'),
                p('It ships with Teme and is the theme a new site starts with. As a package it is ', c('teme/theme-default'), "; another theme is added the same way, with Composer. A theme's templates are checked with:"),
                snippet('bash', 'php artisan teme:theme:check'),
                h3('Versions'),
                p(b('0.1.0'), ', for Teme 0.1 up to, not including, 1.0: the theme as it ships with 0.7.0-alpha.1.'),
                h3('Source'),
                p('Made by the Teme team, under the MIT licence.'),
                p(a('Source on GitHub', 'https://github.com/temecms'), ' · ', a('Theme reference', '/docs/')),
            ],
        },
    },
    {
        key: 'second-theme',
        type: 'theme',
        title: 'A second theme',
        slug: 'second-theme',
        excerpt: lead2,
        fields: { badge: 'Planned', meta: 'Teme team', status: 'Planned, not started' },
        body: {
            type: 'doc', content: [
                p(lead2),
                p('Planned · Example'),
                ul(
                    [b('Status'), ' Planned, not started'],
                    [b('Made by'), ' The Teme team'],
                    [b('Version'), ' None yet'],
                ),
                hr(),

                h3('Pictures'),
                p('Its front page and an article, wide and on a phone, light and dark.'),
                h3('What it supports'),
                p('Its menus, regions, settings and templates, read from its manifest.'),
                h3('Install and versions'),
                p('The package to add, and each release with the Teme versions it works with.'),
                p(a("See Teme Default's page", 'theme:teme-default')),
            ],
        },
    },
];
