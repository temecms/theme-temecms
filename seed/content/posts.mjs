import { doc, p, h2, a, c, ul } from '../lib.mjs';

export default [
    {
        key: 'release-0-7-0-alpha-1',
        type: 'post',
        title: 'Teme 0.7.0-alpha.1: bylines',
        slug: 'teme-0-7-0-alpha-1-bylines',
        excerpt: 'An item can now credit several people, in order, and a person needn’t have an account. With this release the foundations of every area are in place.',
        publishedAt: '2026-11-13T09:00:00Z',
        category: 'Release notes',
        body: doc(
            h2('Bylines'),
            p('A story can credit a news agency, a guest writer, or a writer who has left. Deleting an account keeps the departed writer’s name on their work, and their author page answers at the same address.'),
            p('There is no screen for credits yet. They are set from the console, with ', c('teme:people:add'), ' and ', c('teme:bylines:set'), '; the screens come in a later release.'),
            h2('For developers'),
            ul(
                ['The read API’s ', c('authors'), ' is every person an item credits, in order; ', c('author'), ' is the first.'],
                ['Themes get the item’s credits as ', c('$authors'), '.'],
                ['The export carries a ', c('bylines'), ' file.'],
            ),
            h2('Updating'),
            p('Update to 0.6.0-alpha.2, or 0.6.0-alpha.1, before this release, then run the migrations. They add tables and one index, and change no existing row.'),
            p('The full notes are in the changelog on ', a('GitHub', 'https://github.com/temecms'), '.'),
        ),
    },
];
