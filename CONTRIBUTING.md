# Working on the theme

Notes for the maintainers. Site owners need only the [README](README.md).

You need Node.js 20.11 or later to rebuild the assets, compile the content or run the browser checks, and Google Chrome for the browser checks (they drive it through Playwright).

## A local copy

To run Teme on a local copy of the theme, put the copy in a folder named `temecms` (a theme loaded from a folder must sit in a folder named after its code) and add that folder, as an absolute path, to `TEME_THEME_PATHS` in the Teme install's `.env`.

## Build the assets

The built assets in `public/build` are committed, so an install needs no build step. After changing anything under `assets/`:

```bash
npm ci
npm run build          # Vite writes public/build
```

## The seed

The `seed` folder holds the whole temecms.com site. The content is Tiptap JSON (never HTML), written in `seed/content/*.mjs` with the helpers in `seed/lib.mjs` and compiled to `seed/content.json`, which is committed. After editing `seed/content/*.mjs`:

```bash
node seed/build-content.mjs        # seed/content/*.mjs -> seed/content.json
```

`seed/seed.php` makes everything through Teme's own Operations, never in SQL, acting as the administrator `SEED_ADMIN_ID` names (default `1`): signed in, with a password confirmed just now, as the admin's own requests are. It needs only Teme itself, not Teme's testing kit, so it runs on an install made with `composer install --no-dev`. Run it from the Teme install's folder:

```bash
SEED_ADMIN_ID=1 php /path/to/temecms/seed/seed.php [step ...]
```

With no steps it runs them all, in this order, whatever order they are named in:

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

Every step checks first and does nothing when its work is already done, so a second run changes nothing. What it made is recorded in a state file, `storage/app/theme-temecms-seed.json` in the Teme install (an installed theme's folder is read-only); `SEED_STATE` names another file. Content and pictures are found by that file, and content also by its title, so a rerun never makes a second copy.

Published items and regions are left alone unless their type is named in `SEED_FORCE`, as in `SEED_FORCE=page,doc,region`, which writes them again from `seed/content.json` and publishes them.

A refusal (a validation error, a missing permission, or a missing password confirmation) prints the step that was refused and why, and the seed exits with a non-zero code. So does any other failure.

## Tests

The theme's tests use Teme's testing kit, so they run in a Teme install made with its development packages. From the Teme install's folder:

```bash
php artisan test /path/to/temecms/tests
```

They check what the theme alone decides: which template answers each address, the one `h1`, the footer's words and the follow link from the theme's settings.

Browser checks of the seeded site, at 1280 and 375 wide: status 200, one `h1`, heading order, alt text and image sizes, no sideways scroll, and no axe violation. Point `BASE` at the site; it defaults to `http://localhost:8000`:

```bash
npm ci
BASE=http://localhost:8000 node tests/pages.mjs          # report in tests/.output/report.json
BASE=http://localhost:8000 node tests/screenshots.mjs    # full-page shots into docs/screenshots
```

## Releasing

Raise the version in both `composer.json` and `teme-theme.json`, and add the release to `CHANGELOG.md`.
