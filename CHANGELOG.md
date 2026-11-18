# Changelog

## 0.1.2

- The seed runs on a live Teme site made from Teme's image: it no longer needs Teme's testing kit, which a production install doesn't have. It acts as the administrator named in `SEED_ADMIN_ID` itself, and a refused step says which step and why, and stops with a non-zero exit code.
- The seed's state file moved out of the theme's folder, which is read-only on a live site, to `storage/app/theme-temecms-seed.json` in the Teme install (`SEED_STATE` names another file).
- A second run of the seed changes nothing: every step checks first, including the theme, menus, regions, settings and addresses.
- With no steps named, the seed runs them all, and always in their proper order, so the theme is active before the pages that pick its templates and before the menus go in its slots.
- The footer's status line is empty by default, and the footer and the home page leave it out while it is empty.
- The README says how to load the site on a live install; notes for working on the theme moved to `CONTRIBUTING.md`.

## 0.1.1

- Installs on Teme 1.x: the theme requires Teme `^1.0`.

## 0.1.0

- The TemeCMS website theme: the product home page, documentation, plugin and theme directories, a blog, and the seed that loads the temecms.com site.
