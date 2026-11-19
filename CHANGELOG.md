# Changelog

## 0.2.0

The theme is only the looks again. temecms.com's content moved to the new **temecms-site** plugin (`temecms/temecms-site`): the content types Documentation, Plugin directory and Theme directory with their fields, and the seed that loads the pages, posts, guides, listings, pictures, regions, menus and settings. Switching themes no longer takes the site's types with it, and the admin's menu no longer shows Plugins and Themes twice.

- The `seed` folder is gone, and the README and CONTRIBUTING no longer describe seeding; they point to the plugin.
- The documentation pages' version label shows the Teme release the site runs when the Documentation version label setting is empty, which is now its default. With a Teme too old to say its release, an empty setting shows no label.
- The status line's description gives a current example.
- The templates keep their names: they follow the type keys `doc`, `plugin` and `theme`, which the plugin keeps.

What a site owner does: update Teme to 1.2.0, install and enable the temecms-site plugin (it takes over the types the old seed made, with all their content), then update this theme to 0.2.0. Nothing is loaded again. If you once set the Documentation version label, clear it under Site › Themes to show the running release. The plugin's README has the steps, under *Moving an existing site*.

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
