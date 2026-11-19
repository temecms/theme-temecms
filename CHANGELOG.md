# Changelog

## Unreleased

The free plugin and theme directory, as drawn: the theme draws the fields the temecms-site plugin (1.1.0) fills from the directory's index.

- **Plugins** and **Themes** are two pages. Search, categories with their counts, "Works with Teme", sort, the count, equal cards (pictures for themes) and page numbers; on the first page, the strips Recently updated, New and Picked by the Teme team. Without the script every listing shows.
- **A plugin's page**: its header with Install and Source, five tabs (Overview, Screenshots, Changelog, Permissions, Support) and the facts beside them, with "Ratings and reviews: later", more from the author, and Report this listing.
- **A theme's page**: a gallery in a laptop and a phone frame, and Preview before Install.
- **Submit a plugin or theme**, a page template: the form, which the plugin turns into a pull request on the directory, the four public steps and the rules in brief. The plugin's answer shows on the page.
- **Showcase**: sites built with Teme, by kind, each with its theme and plugins, and the form to add a site.
- **The documentation** in three columns: the tree with a version switcher, the article, "On this page". Search with Ctrl K, ⌘ K or /; code with its language and Copy; Note, Tip and Warning; previous and next; "Was this page helpful?". The docs home has a card per audience, popular pages and what's new.
- New settings: the addresses of Plugins, Themes, Submit, the docs home and the guides they link to, the documentation's versions, and where "this page didn't help" goes. New menu slots `docs` and `docs-popular`, and the region `docs-whats-new`.
- The regions `plugins-intro`, `plugins-how`, `themes-intro` and `themes-build` are no longer drawn; they stay declared so what they hold isn't lost.
- The browser checks run light and dark, and `tests/states.mjs` checks the pages' open states (search, preview, tabs, the forms' answers).

What a site owner does: update the temecms-site plugin to 1.1.0 and run its directory sync, update this theme, make a page with the template Submit a plugin or theme at `/submit/`, and fill the menu `docs-popular` and the region `docs-whats-new` if you want those sections.

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
