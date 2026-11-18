# TemeCMS: the theme of temecms.com

The theme of [temecms.com](https://temecms.com), the TemeCMS project's own website: a product home page, documentation, plugin and theme directories, a migration page, hosting, developers, FAQ, roadmap and a blog.

- Package: `temecms/theme-temecms`
- Theme code: `temecms`
- Licence: MIT

![The home page at 1280 wide](docs/screenshots/home-1280.png)

## Requirements

- TemeCMS `^1.0`

Working on the theme itself (assets, content, browser checks) needs more: see [CONTRIBUTING.md](CONTRIBUTING.md).

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
| Documentation version label | 0.7 |

## Load the site on a live install

The theme carries the whole temecms.com site in its `seed` folder: pages, posts, guides, plugin and theme entries, product pictures, menus, regions and settings. One command puts it into a Teme install. It makes everything through Teme's own Operations, as an administrator would from the admin, and needs nothing that isn't in Teme's production image.

Before you start:

- the theme is installed (see [Install](#install));
- an administrator exists. If none does yet, make one with `php artisan teme:user:create --role=administrator --name="Your Name" --email=you@example.com` and open the link it prints. You need that administrator's user ID: the first user on a new site is `1`.

Then, on Teme's Docker image (as with the recipe on Dokploy):

1. Open a terminal in the **web** container (in Dokploy: the `web` service's Docker terminal; or `docker compose exec web sh` next to the compose file). The image's `php` runs as the site's own user, so the pictures the seed uploads stay writable by the site.
2. Go to Teme's folder:

   ```bash
   cd /var/www/html
   ```

3. Run the seed. A theme installed from the admin lives in the extensions folder, under `current/vendor/temecms/theme-temecms` (installed another way, use the theme's own folder). Set `SEED_ADMIN_ID` to your administrator's ID:

   ```bash
   SEED_ADMIN_ID=1 php /var/teme/extensions/current/vendor/temecms/theme-temecms/seed/seed.php types fields media theme content regions menus settings addresses activate
   ```

The steps run in this order, whatever order you name them in:

| Step | Does |
|---|---|
| `types` | Adds the content types Guide (`/docs/{slug}/`), Plugin (`/plugins/{slug}/`) and Theme |
| `fields` | Adds their fields, and the page fields kicker and emphasis |
| `media` | Uploads the product pictures in `seed/media` with their alt text |
| `theme` | Makes `temecms` the site's theme (before `content`: a page picks its template from the active theme) |
| `content` | Makes and publishes every page, post, guide, plugin and theme entry, with links between them stored as IDs |
| `regions` | Fills the theme's regions |
| `menus` | Makes the menus and puts them in the theme's slots |
| `settings` | Sets the site's title and tagline |
| `addresses` | Moves the theme directory to `/theme-directory/{slug}/` (core serves theme assets from `/themes/`) |
| `activate` | Makes the home page the front page and the blog page the posts page |

`theme`, `settings`, `addresses` and `activate` change the site's theme, title, addresses and front page, so run them only on a site meant for this content. Leave a step out to skip it.

It is safe to run again. What the seed made before is found and reused, never duplicated, and a second run changes nothing: it ends with "Nothing to change". It remembers what it made in `storage/app/theme-temecms-seed.json` (set `SEED_STATE` to keep that file elsewhere); without that file it finds its pictures and items by name. Published items are left as they are, even if you have edited them since; to write the seed's version over a type's items again, name the type in `SEED_FORCE`, as in `SEED_FORCE=page,doc` (`region` for the regions).

If Teme refuses a step, the seed prints the step and the reason, such as `Refused: Publishing Hosting. …`, stops, and exits with a non-zero status. Fix the cause and run the same command again: it carries on where it stopped.

## Working on the theme

Building the assets, changing the seed's content, the tests and the browser checks are in [CONTRIBUTING.md](CONTRIBUTING.md). What changed in each version is in [CHANGELOG.md](CHANGELOG.md).

## Licence

MIT, copyright Krafty Sprouts Media, LLC. See [LICENSE](LICENSE). The IBM Plex fonts in `public/fonts` are under the SIL Open Font License; see `public/fonts/OFL-ibm-plex.txt`.
