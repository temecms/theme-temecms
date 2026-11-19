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
| `archive-doc`, `single-doc` | The documentation home, and a guide with its tree, proof mark, "On this page", previous and next |
| `archive-plugin`, `single-plugin` | The plugin directory (search, categories, rows) and a plugin's page |
| `archive-theme`, `single-theme` | The theme directory and a theme's page |
| `latest`, `single`, `archive`, `search` | The blog, a post, categories and tags, search |
| `notfound`, `error`, `index` | Not found, errors, the fallback |

Page templates an editor can pick: Default, Home, Landing (an opening, then sections), Questions and answers, Roadmap (three columns).

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
| `docs-editors`, `docs-operators`, `docs-developers` | The documentation's three guides |

## Regions

| Region | Where | Cards |
|---|---|---|
| `home-latest` | Home page: from the blog | featured posts (one) |
| `after-content` | After the content of a page or post | callout, button, site code, featured posts |
| `blog-intro` | Blog: opening text | callout, button, image |
| `docs-intro` | Documentation: opening text | callout, button, image |
| `plugins-intro` | Plugins: opening text | callout, button, image |
| `plugins-how` | Plugins: how they work | callout, button, image |
| `themes-intro` | Themes: opening text | callout, button, image |
| `themes-build` | Themes: build a theme | callout, button, image |

Fill them under Content › Regions.

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

## The site's content

The theme is only the looks. temecms.com's content types (Documentation, Plugin directory and Theme directory, with their fields), its starting content (pages, posts, guides, listings, pictures, regions and menus) and anything else the site does on its own live in the **temecms-site** plugin, `temecms/temecms-site`, so switching themes never makes content disappear. Install and enable that plugin, then load the content with its seed: its README says how, and how to move a site that the theme's seed (0.1.x) loaded.

The templates for the directories and the documentation keep following the plugin's type keys: `doc`, `plugin` and `theme`.

## Working on the theme

Building the assets, the tests and the browser checks are in [CONTRIBUTING.md](CONTRIBUTING.md). What changed in each version is in [CHANGELOG.md](CHANGELOG.md).

## Licence

MIT, copyright Krafty Sprouts Media, LLC. See [LICENSE](LICENSE). The IBM Plex fonts in `public/fonts` are under the SIL Open Font License; see `public/fonts/OFL-ibm-plex.txt`.
