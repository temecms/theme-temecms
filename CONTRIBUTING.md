# Working on the TemeCMS theme

Notes for whoever maintains this theme. Site owners need only the [README](README.md).

## What you need

- A Teme install (`^1.0`) with its database migrated, and this theme's folder in its `TEME_THEME_PATHS`, as an absolute path. Path themes must match their folder name, so the folder is named `temecms`.
- Node.js 20.11 or later, to rebuild the assets or run the browser checks.
- Google Chrome, for the browser checks (they drive it through Playwright).

## Build the assets

The built assets in `public/build` are committed, so an install needs no build step. After changing anything under `assets/`:

```bash
npm ci
npm run build          # Vite writes public/build
```

## The site's content

The theme carries no content. The content types, their fields and the starting content are in the temecms-site plugin (`temecms/temecms-site`), with the seed that loads them; change the content there.

## Tests

The theme's tests use Teme's testing kit, which a development install has. From the Teme install's folder:

```bash
php artisan test /path/to/temecms/tests
```

They check what the theme alone decides: which template answers each address, the one `h1`, the footer's words and the follow link from the theme's settings.

Browser checks of a site loaded with the temecms-site plugin's starting content and its directory, at 1280 and 375 wide, light and dark: status 200, one `h1`, heading order, alt text and image sizes, no sideways scroll, and no axe violation. `tests/states.mjs` does the same for the pages' open states: the docs search, the docs menu on a phone, a filtered list, a plugin's tabs and install steps, the theme preview, Submit's answers and errors, and Showcase's form. Point `BASE` at the site; it defaults to `http://localhost:8000`. Name pages or states to check only those, and set `CHROME` to a Chrome or Chromium binary when Chrome isn't installed:

```bash
npm ci
BASE=http://localhost:8000 node tests/pages.mjs          # report in tests/.output/report.json
BASE=http://localhost:8000 node tests/states.mjs
BASE=http://localhost:8000 node tests/screenshots.mjs    # full-page shots into docs/screenshots
```

## Releasing

Raise the version in both `composer.json` and `teme-theme.json`, and add the version's entry to [CHANGELOG.md](CHANGELOG.md).
