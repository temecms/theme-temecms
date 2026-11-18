# TemeCMS: the theme of temecms.com

The theme of [temecms.com](https://temecms.com), the TemeCMS project's own website: a product home page, documentation, plugin and theme directories, a migration page, hosting, developers, FAQ, roadmap and a blog.

- Package: `temecms/theme-temecms`
- Theme code: `temecms`
- License: MIT

![The home page at 1280 wide](docs/screenshots/home-1280.png)

## Requirements

- Teme 1.x

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
| Status line (foot of every page; empty shows none) | empty |
| Licence line (empty shows none) | MIT licence |
| Show the latest blog posts on the home page | off |
| Documentation version label | 0.7 |

## Load the site on a live install

The theme carries the whole temecms.com site: its pages, blog posts, documentation, plugin and theme directories, product pictures, menus and regions. One command puts all of it on a Teme site that runs from Teme's image.

Before you start:

- install the theme (see above);
- make sure the site has an administrator. The first administrator, the one made when the site was set up, has the ID `1`. To make the content as another administrator, use that user's ID in place of `1` below.

Then open a terminal in the site's web container (in Docker Compose, `docker compose exec web sh`; on a hosting panel, the web service's terminal) and run:

```bash
cd /var/www/html
SEED_ADMIN_ID=1 php /var/teme/extensions/current/vendor/temecms/theme-temecms/seed/seed.php
```

It prints a line for each thing it makes. When it finishes, the site uses this theme, its home page is the product home page, the blog page lists the posts, and the menus are in place. It also sets the site's title and tagline.

This changes the site's theme, title, tagline and home page, so run it only on a site meant to be temecms.com.

It is safe to run again: anything already made is left as it is, and a second run changes nothing. It remembers what it made in `storage/app/theme-temecms-seed.json`; leave that file in place.

If a step is refused, it says which step and why, and stops without going further. The usual reason is that the user named in `SEED_ADMIN_ID` isn't an administrator.

## License

MIT, copyright Krafty Sprouts Media, LLC. See [LICENSE](LICENSE). The IBM Plex fonts in `public/fonts` are under the SIL Open Font License; see `public/fonts/OFL-ibm-plex.txt`.
