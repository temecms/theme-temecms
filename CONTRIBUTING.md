# Working on the TemeCMS theme

Notes for whoever maintains this theme. Site owners need only the [README](README.md).

## What you need

- A Teme install (`^1.0`) with its database migrated, and this theme's folder in its `TEME_THEME_PATHS`, as an absolute path. Path themes must match their folder name, so the folder is named `temecms`.
- Node.js 20.11 or later, to rebuild the assets, compile the seed's content or run the browser checks.
- Google Chrome, for the browser checks (they drive it through Playwright).

## Build the assets

The built assets in `public/build` are committed, so an install needs no build step. After changing anything under `assets/`:

```bash
npm ci
npm run build          # Vite writes public/build
```

## The seed

`seed/seed.php` builds the whole temecms.com site through Teme's own Operations, never in SQL; the README's *Load the site on a live install* is the site owner's side of it.

- The content is Tiptap JSON (never HTML), written in `seed/content/*.mjs` with the helpers in `seed/lib.mjs`, and compiled to `seed/content.json`, which is committed. After editing `seed/content/*.mjs`:

  ```bash
  node seed/build-content.mjs        # seed/content/*.mjs -> seed/content.json
  ```

- The seed runs on Teme's production image, which is installed without Composer's dev packages. So it must not use Teme's testing kit (`Teme\Testing`) or anything from PHPUnit. It acts as the administrator `SEED_ADMIN_ID` the way the admin's own requests do: that user signed in, with core's `SessionElevation` over a request whose session holds a password confirmed now. A refusal (`ValidationException`, `AuthorizationException`, `ElevationRequiredException`) prints the step and the reason, and exits with status 1.
- It loads Teme as `artisan` does, `bootstrap/extensions.php` included, so a theme installed from the admin (under `TEME_EXTENSIONS_PATH`, in `current/vendor/temecms/theme-temecms`) is registered when it runs.
- Every step must be repeatable, and a second run must change nothing: look for what exists (by slug, by title, by the state file, or by the setting's current value) before writing. Check a change to a step by running the seed twice against a scratch database and comparing a data-only `pg_dump` from before and after the second run.
- Its state file is `storage/app/theme-temecms-seed.json`, or `SEED_STATE`. The theme's folder is read-only on a live site, so nothing is written there. A `seed/.state.json` from 0.1.1 or earlier is still read on a local copy when the new file doesn't exist yet.

On a local copy, from the Teme install's folder:

```bash
SEED_ADMIN_ID=1 php /path/to/temecms/seed/seed.php types fields media theme content regions menus settings addresses activate
```

## Tests

The theme's tests use Teme's testing kit, which a development install has. From the Teme install's folder:

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

Raise the version in both `composer.json` and `teme-theme.json`, and add the version's entry to [CHANGELOG.md](CHANGELOG.md).
