import { b, c, p, a, h2, kicker, ul, callout, cta, image, doc, nodeId } from '../lib.mjs';

export default {
    'docs-intro': doc(
        p('Guides tested against the code they describe, so what they say is what the release does.'),
        callout('note', "The documentation site opens with Teme's first public release. The guides marked here as written are in the repository today, at 0.7.0-alpha.1."),
        h2('What these marks mean'),
        ul(
            [b('Checked by CI'), " The image's automated build runs the page's steps."],
            [b('Checked by tests'), ' Automated tests check the page against the code: its commands, settings, files and services exist.'],
            [b('Run on Dokploy'), ' Someone installed Teme from the page on a real server of that platform, and it ran.'],
            [b('Not yet proved'), ' Nothing has run it yet. The page opens by saying what is not known.'],
        ),
    ),

    'plugins-intro': doc(
        p("A plugin adds a feature to your site: a tool for editors, a check before publishing, a way to share stories. It runs only once an administrator agrees to what it asks for."),
        callout('note', 'No plugin is published yet. The directory opens when Teme is public. Below are the first-party plugins the Teme team plans to build first.'),
        p('For developers: a plugin is a Composer package with a manifest.'),
        cta('Build a plugin', 'page:developers'),
        cta('Publish a plugin', 'page:publish-a-plugin'),
    ),

    'plugins-how': doc(
        kicker('How plugins work'),
        h2('Asked, agreed, then running.'),
        p('Before a plugin runs, an administrator sees what it asks to do and what it listens for, and agrees or not.'),
        ul(
            'A plugin reaches outside services only through Teme, to the hosts it was granted',
            'An update that asks for more waits for a new yes',
            'Disabling one takes a click',
            "Installing from the admin is planned; today the server's operator adds the package",
        ),
        image('consent', { alt: 'The dialog the admin shows before a plugin is enabled: what the plugin asks to do and what it listens for, with buttons to agree or cancel.', caption: 'The enable dialog, drawn from Site › Plugins.' }),
    ),

    'themes-intro': doc(
        p('A theme decides how the public site looks, and nothing else. Themes are Composer packages, built against a theme contract that is written down and tested.'),
        callout('note', 'Teme ships with one theme today. The directory opens when Teme is public.'),
    ),

    'themes-build': doc(
        kicker('Build a theme'),
        h2('Blade templates, a manifest, nothing more.'),
        p('A theme reads its pages from typed view data, declares its menus, regions and settings in ', c('teme-theme.json'), ', and is checked against a published schema.'),
        cta('For developers', 'page:developers'),
        cta('Theme reference', '/docs/'),
    ),
    'blog-intro': doc(
        p('Announcements and release notes. The full changelog of every release is on ', a('GitHub', 'https://github.com/temecms'), '.'),
    ),
    'home-latest': doc(
        { type: 'featured-posts', attrs: { id: nodeId(), heading: 'From the blog', source: 'latest', items: [], type: 'post', termId: null, count: 3, images: false } },
    ),
};
