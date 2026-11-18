# Changelog

## 0.1.2

- The seed runs on a live Teme install made from the production image. It no longer needs Teme's testing kit or PHPUnit: it acts as the administrator `SEED_ADMIN_ID` itself, and a refused step prints the step and the reason and exits with a non-zero status.
- The seed finds a theme installed from the admin, under the extensions folder.
- The seed's state file is `storage/app/theme-temecms-seed.json` (or `SEED_STATE`), no longer inside the theme's folder, which is read-only on a live site.
- A second run of the seed changes nothing. Regions, menus, menu places, settings, the theme, the theme directory's address and the front page are written only when they differ, and pictures and items an earlier run made are found again without the state file.
- The steps run in one fixed order, the theme before the content, whatever order they are named in. An unknown step name stops the seed before it starts.
- The status line is empty by default, and the footer and the home page leave it out when it is empty.
- The README says how to load the site on a live install; notes for maintainers moved to `CONTRIBUTING.md`.

## 0.1.1

- Installs on Teme 1.x: the theme requires Teme `^1.0`.

## 0.1.0

- The theme of temecms.com: the product home page, documentation, plugin and theme directories, a migration page, hosting, developers, FAQ, roadmap and a blog, with the seed that builds the site.
