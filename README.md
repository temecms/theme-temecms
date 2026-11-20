# TemeCMS: the theme of temecms.com

The theme of [temecms.com](https://temecms.com), the TemeCMS project's own website: a product home page, documentation, plugin and theme directories, a migration page, hosting, developers, FAQ, roadmap and a blog.

- Package: `temecms/theme-temecms`
- Theme code: `temecms`
- Licence: MIT

![The home page at 1280 wide](docs/screenshots/home-1280.png)

## Requirements

- TemeCMS `^1.0`

Working on the theme itself (assets, browser checks) needs more: see [CONTRIBUTING.md](CONTRIBUTING.md).

## Install

From the admin: open the theme installer, choose to install from a Git address, and give it

```
https://github.com/temecms/theme-temecms
```

Or with Composer, in the Teme install's folder (once the package is on Packagist):

```bash
composer require temecms/theme-temecms
```

Then check it and link its public files:

```bash
php artisan teme:theme:check temecms
php artisan teme:themes:link
```

and activate it under Site › Themes.

To work on a local copy instead, add the theme's folder, as an absolute path, to `TEME_THEME_PATHS` in the Teme install's `.env`. The folder must be named `temecms`, the theme's code.

## What it draws

| Template | Draws |
|---|---|
| `single-page-home` | The home page: opening, four stops, closing |
| `single-page-landing`, `-faq`, `-roadmap`, `single-page` | Pages made of an opening and sections; questions; three columns; plain pages |
| `archive-doc`, `single-doc` | The documentation home (a card per audience, popular pages, what's new) and a guide, in three columns: the tree with the version switcher, the article, "On this page". Search with Ctrl K (⌘ K, or /); code with its language and Copy; Note, Tip and Warning; previous and next; "Was this page helpful?" |
| `archive-plugin`, `single-plugin` | Plugins: search, categories with counts, "Works with Teme", sort, counts, page numbers, and on the first page the strips Recently updated, New and Picked by the Teme team. A plugin's page: its header with Install and Source, the tabs Overview, Screenshots, Changelog, Permissions and Support, and the facts beside them (the review, version, releases, Teme range, categories, package, GitHub, "Ratings and reviews: later", the author with more from them, and Report this listing) |
| `archive-theme`, `single-theme` | Themes, the same as Plugins with picture cards. A theme's page: a gallery in a laptop and a phone frame, Preview before Install, Overview and Changelog, and the facts |
| `archive-showcase` | Showcase: sites built with Teme, by kind, each with its theme and plugins, and the form to add yours |
| `latest`, `single`, `archive`, `search` | The blog, a post, categories and tags, search |
| `notfound`, `error`, `index` | Not found, errors, the fallback |

Page templates an editor can pick: Default, Home, Landing (an opening, then sections), Questions and answers, Roadmap (three columns), and Submit a plugin or theme (the form, the four public steps and the rules in brief).

The directory, Submit and Showcase read the temecms-site plugin's fields (see its README, *The directory*): a listing's summary, icon, categories, latest version, Teme versions, author, links, stars, issues, releases, permissions, screenshots and changelog, and a showcase site's address, kind, screenshot, theme and plugins. The README is the listing's body. A listing's categories are keys; the theme words the directory's own (SEO, Editing, Content and so on) and takes any other from the category's term. The Submit and Showcase forms post to the plugin, which answers back to the page with `?submitted=…` or `?error=…`; the theme's script shows the answer. Without the script every listing shows, unfiltered.

### How a page's content becomes sections

Landing, home, FAQ and roadmap pages split their content at horizontal rules: the first part is the opening, every later part a section. Inside a section:

- a paragraph that is only bold text is the small label above the section's heading;
- an `h2` is the section's heading; `h3` headings followed by paragraphs become cards (two or more make a grid);
- a paragraph that is only a bold link is drawn as a quiet button;
- a numbered list is drawn as steps, and a picture as a picture with its caption.

## Menus

| Slot | Where |
|---|---|
| `primary` | The header |
| `footer-teme`, `footer-build`, `footer-project` | The footer's three columns |
| `docs-editors`, `docs-operators`, `docs-developers` | The documentation's three guides, and the first page of each on the docs home's audience cards |
| `docs` | The documentation's tree: each heading (or item with pages under it) a group. Empty, the tree is the three guides' menus, each of their groups a heading |
| `docs-popular` | The docs home's popular pages |

## Regions

| Region | Where | Cards |
|---|---|---|
| `home-latest` | Home page: from the blog | featured posts (one) |
| `after-content` | After the content of a page or post | callout, button, site code, featured posts |
| `blog-intro` | Blog: opening text | callout, button, image |
| `docs-intro` | Documentation: opening text | callout, button, image |
| `docs-whats-new` | Documentation home: what's new (empty leaves the section out) | callout, button, image |

Fill them under Content › Regions. The regions `plugins-intro`, `plugins-how`, `themes-intro` and `themes-build` are no longer drawn: the directory pages say their own words, as drawn. They stay declared so what they hold isn't lost.

## Settings

Under Site › Themes:

| Setting | Default |
|---|---|
| Maker line (footer) | Made by Krafty Sprouts Media. |
| The follow link's words | Follow the build |
| The follow link's address (https only) | https://github.com/temecms |
| Status line (foot of every page and the home page; empty shows none) | empty |
| Licence line (empty shows none) | MIT licence |
| Show the latest blog posts on the home page | off |
| Documentation version label (empty shows the Teme release the site runs) | empty |
| Documentation versions, latest first, as `label=address` separated by commas (`1.2=/docs/, 1.1=/docs/1.1/`); with two or more the tree shows a switcher, and an older version's pages a banner | empty |
| The documentation home's address | /docs/ |
| Where "this page didn't help" goes | https://github.com/temecms/teme/issues/new |
| The Plugins page's address | /plugins/ |
| The Themes page's address | /theme-directory/ |
| The Submit page's address (the page with the template Submit a plugin or theme) | /submit/ |
| Writing a plugin, Making a theme, the directory's full rules: their addresses (empty hides the link) | empty |

## The site's content

The theme is only the looks. temecms.com's content types (Documentation, Plugin directory and Theme directory, with their fields), its starting content (pages, posts, guides, listings, pictures, regions and menus) and anything else the site does on its own live in the **temecms-site** plugin, `temecms/temecms-site`, so switching themes never makes content disappear. Install and enable that plugin, then load the content with its seed: its README says how, and how to move a site that the theme's seed (0.1.x) loaded.

The templates for the directories and the documentation keep following the plugin's type keys: `doc`, `plugin` and `theme`.

## Working on the theme

Building the assets, the tests and the browser checks are in [CONTRIBUTING.md](CONTRIBUTING.md). What changed in each version is in [CHANGELOG.md](CHANGELOG.md).

## Licence

MIT, copyright Krafty Sprouts Media, LLC. See [LICENSE](LICENSE). The IBM Plex fonts in `public/fonts` are under the SIL Open Font License; see `public/fonts/OFL-ibm-plex.txt`.
