# Changelog

All notable changes to `filament-relation-navigator` are documented here.

## Unreleased

### Fixed

- The "Open as page" action is added only to the relation managers that have a relation page, their own or their group's. Before, every relation manager of the app got it, hidden, first in the header of its table: a test or a piece of code that took the first header action found it instead of its own.

### Documentation

- The documentation site reads better on phones: the screenshot under the heading at full width, tables as one card per row, long inline code wrapping, and the Arabic pages no longer wider than the screen.

## 1.8.0 - 2026-11-14

25 more languages in the package, and the documentation in eight more. No API changes.

### Added

- Translations for Arabic, Bulgarian, Catalan, Chinese (simplified and traditional), Czech, Danish, Finnish, Greek, Hebrew, Hungarian, Indonesian, Japanese, Korean, Norwegian, Persian, Polish, Portuguese (Portugal), Romanian, Russian, Slovak, Swedish, Turkish, Ukrainian and Vietnamese: 32 languages, with Filament's codes.
- In right to left languages the previous and next arrows turn around, as in Filament's pagination.

### Documentation

- The documentation and the README in Spanish, Portuguese, French, German, Chinese, Japanese, Russian and Arabic (right to left), besides English and Italian. English is the reference: every translated page says so and links to it.

## 1.7.0 - 2026-11-14

Previous and next record on nested resources. No API changes.

### Added

- Previous and next record on nested resources, within the same parent record: from one of a customer's orders to the next order of the same customer, on the view, edit and relation pages.
- On a nested resource they follow the parent's relation manager table the user came from (sort, filters, search), when the nested resource is its related resource (`$relatedResource`). Not with the `cookie` session driver, which keeps the parent's list only.

## 1.6.2 - 2026-11-14

A keyboard fix in the menu, and documentation for every minor version. No API changes.

### Fixed

- Opening the menu with a click no longer takes the focus back to the search field when the arrow keys have already moved it to a page. Menu views you published keep the old behaviour until you publish them again.

### Documentation

- Documentation for every minor version, at `/vX.Y/` and in the versions menu: from its own site where it had one (1.4 onwards), else its README and changelog.
- Screenshots of global search links, "Open as page" and the relation page header, and a GIF of previous and next record, in the README and the docs.
- A banner on documentation of versions with a published security advisory: fixed and not dismissible for critical and high, dismissible for medium and low. Older versions without advisories say that a newer one exists.

## 1.6.1 - 2026-11-14

Fixes from a review of the whole package, faster relation pages, and the documentation site. No API changes.

### Fixed

- "Open as page" links only to a relation page the user can open: the relation manager's `canViewForRecord()` and the resource's `canAccessRelationPage()` were not checked, so the link could end in a 403. It picks the best page the user can open.
- Relation group badges: the color callback receives the badge, as in the tab, and a lazy group badge (`deferBadge()`) is lazy in the menu too.
- Menu views that cannot load badges later (published before 1.3, presets of your own) get lazy tab badges with the page too: nothing disappears.
- Shortcuts: the `cmd` alias works everywhere (tooltip, `aria-keyshortcuts`), a shortcut that cannot be matched is no longer advertised, and one returned by a page method is normalized and kept away from the global search like the config's.
- `configureRelationPageAction()` also works as an instance method on a relation manager.
- Previous and next: the remembered list is one compact string (about 170 numeric keys or 30 UUIDs per KB, so up to 100 records as documented), only one list is kept with the `cookie` session driver, records the user can edit but not view are offered, and neighbour queries skip the resource's eager loads.
- The `?relation=` redirect leaves a reload of the view page alone, so its tabs stay reachable.
- Properties of configured relation managers that are models or objects are compared by value, so "Open as page" stays after a table interaction.
- No global search links for nested resources (their URLs need each result's parent).
- A relation group without a string key whose label has no slug (a non-Latin script) gets `group-{key}` instead of no page.
- Resources registered only through a configuration get their Livewire components registered.
- The "Open as page" icon is the same with or without a published config.

### Changed

- Relation pages work out their definitions, context page and class once per request: a relation page reads the resource's `getRelations()` 2 times instead of 66, a view page 3 instead of 19.
- Filament 4.13 is now the lowest version allowed in `composer.json`, as tested since 1.2.1.
- Package plumbing that was public or protected is marked `@internal` (`RelationNavigator::optsIn()`, `registerRoutes()`, `supportsDeferredBadges()`, the remembered-list methods and constants, most of `GeneratedRelationPages`, `RelationPage`'s protected helpers): not covered by semantic versioning from now on. Everything documented in the API reference stays.
- `configureRelationPageActionUsing()` callbacks live in the application container: one list per application.

### Documentation

- The site shows the latest release (home page, menu and footer, read from this changelog) and adds a configuration reference with defaults and levels, an API reference, recipes, troubleshooting, community links and a sitemap, in English and Italian.

## 1.6.0 - 2026-11-14

A security fix for relation pages of resources whose view page is not registered as `view` (or that have none), security hardening from an audit, badges loaded when the menu opens by default, and a customisable "Open as page" action.

### Security

- **Relation pages applied the record's `view` policy only when the view page was registered under the key `view` (or the edit page under `edit`)** (1.3.0 to 1.5.0, [GHSA-mjj3-c8gm-4j73](https://github.com/wit3/filament-relation-navigator/security/advisories/GHSA-mjj3-c8gm-4j73)). With the view page under another key (`'details' => ViewCustomer::route(...)`), or with no view or edit page at all, relation pages checked only `canAccess()` and the relation manager's `canViewForRecord()`. The view page is now also found by its class, and without one the `view` policy applies.
- A policy that denies "as not found" (`Response::denyAsNotFound()`) gives a 404 on relation pages too, as on the view page, instead of a 403 that told existing records apart.
- Generated relation page classes: in the system temporary directory they go to a directory of this user only (0700, owner checked), and a file whose content is not the expected code is rewritten, never included.
- Package methods that the browser does not need are `#[Unexposed]` (Filament 4.16 and later refuse browser calls to them); `getRelationNavigatorBadges()` stays callable and computes once per request.
- `make:filament-relation-navigator-preset` accepts plain names and the package presets only.
- "Open as page" and the tab redirect check the relation page's `canAccess()` before linking to it.

### Changed

- The list remembered for previous and next is one session entry with the five most recent lists, about 1 KB each at most, so cookie sessions keep fitting in 4 KB. A table with `deferLoading()` is never loaded for it.
- `menu.max_height` must be a CSS length (`24rem`, `400px`, `60vh`...); anything else falls back to `24rem`.
- **Menu badges are loaded when the menu opens by default** (`menu.badges` = `'lazy'`): the menu is closed until then, so pages render without the badge queries, also on every Livewire request that redraws the breadcrumbs. Apps that published the config keep what they set. Menu views that cannot load badges later (published before 1.3, or presets of your own) get them with the page, as before. For the old behaviour: `'badges' => true`.

### Added

- `canAccessRelationPage(Model $record, string $slug)` on a resource vetoes a relation page for a record: relation pages, menu, global search and redirect. It is the place for checks a view page makes in its own `authorizeAccess()` or `getRelationManagers()`, which relation pages do not run.
- A relation manager can leave its badge out of the menu, keeping it in its tab: `hasRelationNavigatorBadge()` or `$hasRelationNavigatorBadge` with `InteractsWithRelationPage`.
- `RelationNavigator::supportsDeferredBadges()`.
- **"Open as page" can be customised**: config `relation_manager_action` (`style`: `link`, `button` or `icon_button`; `label`, `icon`, `icon_position`, `color`, `size`, `new_tab`), `RelationNavigator::configureRelationPageActionUsing()` for any change to the Filament action, and `configureRelationPageAction()` on a resource or a relation manager. `RelationNavigator::makeRelationPageAction()` builds it.

### Internal

- CI: checkouts without persisted credentials; the documentation deploys from `main` only.

## 1.5.0 - 2026-11-14

Six improvements: global search links, per-relation titles, "Open as page" on group pages, lazy tab badges in the menu, previous/next that follow the list, and the documentation in Italian. Two behaviour changes, both in "Changed".

### Changed

- **Previous and next follow the list the user came from** (config `record_navigation_follows_list`, default `true`; `shouldRelationNavigatorRecordNavigationFollowList()` on a page or resource): its sort, filters, search and tab, up to 100 records, instead of the key order. They stop at the ends of that list. Records not in the list, or with the option off, still walk by key. The list remembers its records in the session only when record navigation is on in the config or on the resource.
- "Open as page" opens a relation manager's own page before the page of a relation group it also belongs to (it used to open whichever came first in `getRelations()`). A manager that exists only inside a group still opens the group's page.

### Added

- **Documentation in Italian**: [wit3.github.io/filament-relation-navigator/it/](https://wit3.github.io/filament-relation-navigator/it/), from `README.it.md` (same sections as the README, checked by a test).
- **Lazy tab badges stay lazy in the menu**: a relation manager with `$isBadgeLazyLoaded` (or `$isBadgeDeferred` on Filament 4.13) gets its menu badge when the menu opens, the others still come with the page. Filament's default is not lazy, so nothing changes unless you set it. `RelationNavigatorItem::$isBadgeDeferred`, and `RelationNavigator::getItems(..., resolveDeferredBadges: true)` to get every badge now. Published `menu/partials/list.blade.php` views need updating for it.
- **Global search links to relation pages** (config `global_search_actions`: `false` by default, `true`, or the most links per result; resource `hasRelationPageGlobalSearchActions()`; relation manager `hasRelationPageGlobalSearchAction()` or `$hasRelationPageGlobalSearchAction`). `HasRelationPages` adds them to `getGlobalSearchResultActions()`; `RelationNavigator::getGlobalSearchResultActions()` for resources that define it themselves.
- **"Open as page" on relation group pages**: a relation manager shown on its group's page links to its own page when it has one.
- **Title, subheading and breadcrumb of one relation page** from its relation manager, given the owner record: `getRelationPageTitle()`, `getRelationPageSubheading()`, `getRelationPageBreadcrumb()` (static, optional, `null` keeps the default). The menu keeps the tab title.

## 1.4.1 - 2026-11-14

No code changes: documentation, AI tooling and repository hygiene.

### Added

- **Laravel Boost skill** `relation-navigator-development` (`resources/boost/skills/`), next to the guidelines: workflows for relation pages, the menu, options, headers, presets and testing. The guidelines tell agents to use it.

### Documentation

- A documentation site with search: [wit3.github.io/filament-relation-navigator](https://wit3.github.io/filament-relation-navigator/). It is built from the README, the changelog and the config file, so it always matches them. `composer.json` points `homepage` and `support.docs` to it.
- The documentation is published for AI agents too: [llms.txt](https://wit3.github.io/filament-relation-navigator/llms.txt), [llms-full.txt](https://wit3.github.io/filament-relation-navigator/llms-full.txt) and a Markdown version of every page.

### Internal

- GitHub Actions pinned to commit SHAs; Dependabot with a 7-day cooldown, now also for the npm dependencies of the browser tests and of the documentation site.
- `AGENTS.md` (and `CLAUDE.md`) for contributors working with AI agents.

## 1.4.0 - 2026-11-14

Every feature can now be turned on or off globally, per resource and per page or relation manager.

### Added

- **Options per resource**: every option of the menu is read from the page, then from a static method with the same name on the resource, then from the config. For example `public static function hasRelationNavigatorRecordNavigation(): bool` on a resource turns previous/next on for all its pages. Methods: `getRelationNavigatorPreset()`, `isRelationNavigatorSearchable()`, `hasRelationNavigatorBadges()`, `hasDeferredRelationNavigatorBadges()`, `getRelationNavigatorShortcut()`, `hasRelationNavigatorMobileMenu()`, `hasRelationNavigatorRecordNavigation()`, `shouldRedirectRelationNavigatorTabs()`.
- **"Open as page" per resource and per relation manager**: `hasRelationPageLinks()` on a resource, `hasRelationPageLink()` (or `$hasRelationPageLink` with `InteractsWithRelationPage`) on a relation manager, on top of the config `relation_manager_link`.
- **Laravel Boost guidelines** (`resources/boost/guidelines/core.blade.php`): pick the package in `boost:install` and AI agents learn the traits and options.
- `RelationNavigator::addRelationPageAction()`, `hasRelationPageAction()` and the `RELATION_PAGE_ACTION` constant.

### Changed

- **"Open as page" is now an action in the header of the relation manager's table**, next to its title and before its own actions, with an arrow icon. In 1.3.0 it was a link above the table, with an icon that looked like "full screen".

### Deprecated

- `RelationNavigator::renderRelationManagerLink()` and the `relation-manager-link` view: the package no longer uses them. They still render the 1.3.0 link, for a render hook of your own, and will be removed in 2.0.

## 1.3.0 - 2026-11-14

A security fix, new features behind config switches, and a new default look for the menu. Update now if you use policies on your records. No breaking changes: every new key has a default in the code, so a published config from 1.2 keeps working.

### Security

- **Relation pages did not apply the record's `view` policy** (1.0.0 to 1.2.1, [GHSA-v3mh-w7qp-37gc](https://github.com/wit3/filament-relation-navigator/security/advisories/GHSA-v3mh-w7qp-37gc)). Filament checks it when the view page mounts, not in `canAccess()`, and relation pages only called `canAccess()`: a user denied `view` on a record, but allowed `viewAny`, could open its relation pages and see the related records and the header. Relation pages now run the same record check as the page they stand for (`view` for a view page, `update` for an edit page), also on every Livewire request.

### Added

- **Links to relation manager tabs open the relation page** (config `redirect_relation_tabs`, default `false`): `/customers/1?relation=bills` on the view or edit page redirects to `/customers/1/bills`, with `wire:navigate` in SPA mode. Tabs without a relation page, or that the record cannot see, stay where they are. Per page: `shouldRedirectRelationNavigatorTabs()` and `getRelationNavigatorTabRedirectUrl()`.
- **Deferred badges**: `menu.badges` also takes `'lazy'`. The page then renders without the badge queries, and the menu loads the badges when it opens (one request, a spinner meanwhile). New page methods `hasDeferredRelationNavigatorBadges()` and `getRelationNavigatorBadges()`, and `$hasDeferredBadges` for preset views. If you published the package views, update `menu/partials/list.blade.php` before turning it on, or the menu shows no badges.
- **Previous and next record** buttons before the header actions of the pages with the menu (config `record_navigation`, default `false`). They open the neighbour by key on the same page (view, edit, a relation page), or on its first page in the menu when it cannot open that one, and are disabled at the ends. They use the resource's own query, so tenancy and your scopes apply. Per page: `hasRelationNavigatorRecordNavigation()`, `getRelationNavigatorRecordNavigationQuery()`, `getRelationNavigatorRecordNeighbours()`. Nested resources are not supported yet: they show no buttons.
- **"Open as page"** link above the table of every relation manager that has its own page, on the view and edit pages (config `relation_manager_link`, default `true`). It links to the relation group's page for a manager inside a group, and tells apart two entries of the same manager (`PostsRelationManager::make([...])`) by their properties. `RelationNavigator::getRelationManagerPageUrl()` returns the same URL for your own buttons.

- `RelationPage::canAccessRecord()` and `RelationNavigator::canAccessRecordPage()`: the record check of the view and edit pages.

### Fixed

- The menu lists the view and edit pages only when the user can open them for the record (`view` and `update` policies): a user who cannot edit a record no longer sees an Edit item that ends in a 403.

### Changed

- **The default menu is now the `github` preset with the search field** (`menu.preset` = `github`, `menu.searchable` = `true`): it is the one with the most features. Apps that published the config keep what they set. To keep the menu of 1.2 and earlier, set `'preset' => 'simple'` and `'searchable' => false` in `config/filament-relation-navigator.php`, or override `getRelationNavigatorPreset()` and `isRelationNavigatorSearchable()` on the page.

### Internal

- Tests for each feature, tenancy, nested resources and record policies included, and a browser test for the deferred badges. README with a GIF and new screenshots.

## 1.2.1 - 2026-11-14

Bug fixes and tests. No API changes.

### Fixed

- Resources registered through a configuration (`UserResource::make('admins')`) had no generated relation pages: they now get them under the configuration's slug, with its middleware, as Filament does for the resource's own pages.
- On a read-only storage (read-only containers, serverless) the generated page classes go to the system temporary directory. If no directory is writable, the error says how to choose one.
- With a `mod` shortcut, `aria-keyshortcuts` declares both variants (`Meta+…` and `Control+…`) instead of only `Meta`.
- While searching, the separator line no longer shows on top of the list when the block above it is filtered out.

### Internal

- Tests with cached routes, configured resources and `RelationManager::make([...])` entries.
- CI: a job on the lowest allowed versions (Filament 4.13, Laravel 12, Livewire 3.8), browser tests on Filament 4 too, releases published from the changelog when a tag is pushed, Dependabot for the GitHub Actions.

## 1.2.0 - 2026-11-14

### Added

- **Tooltip** on the button that opens the menu: the heading and the shortcut keys, shown as ⌥ ⇧ R on a Mac and Alt Shift R elsewhere.
- **Shortcut** that opens the menu from anywhere on the page with the search field ready, like GitHub's repository switcher. Config `menu.shortcut` (default `alt+shift+r`, `null` turns it off), or `getRelationNavigatorShortcut()` per page. It matches the physical key (any keyboard layout, Option on a Mac), does nothing while typing in a field, opens only the visible menu, and is never registered when it equals one of the panel's global search shortcuts.
- `RelationNavigator::getShortcutKeys()`, `getShortcutCondition()` and `clashesWithFilament()`; `$shortcut` and `$shortcutKeys` for preset views, and the `menu.partials.trigger-attributes` partial for buttons of your own.

### Changed

- The caret of the `github` preset has a light background, lighter on hover, as on GitHub. Run `php artisan filament:assets` after updating.
- `HasRelationNavigator` declares `getRecord()` and `hasRecord()` as abstract. Record pages already have them, so nothing changes for them. A page using the trait without a record, which already failed when rendered since 1.0.1, now fails when its class is loaded, with a clear PHP error; because the menu loads the record pages of the resource, this also breaks the other record pages of that resource until the trait is removed from that page.

## 1.1.0 - 2026-11-14

New features, all backwards compatible.

### Added

- **Small screens**: Filament hides the breadcrumbs below 40rem; there the menu now shows above the page heading, on the pages that use `HasRelationNavigator`. Config `menu.mobile` (default `true`), or `hasRelationNavigatorMobileMenu()` per page. Its few style rules are inline, so it works even if `filament:assets` was not run after the update.
- **Keyboard navigation** in the menu: Down from the search field enters the list, Up and Down move between the pages, Up from the first one goes back to the search field.
- **Separator** between the record pages and the relation pages. Config `menu.separator` (default `true`).
- `RelationNavigatorItem::isRelationPage()`, and `$hasSeparator` for preset views.
- Translations: Spanish, German, French, Brazilian Portuguese and Dutch.

### Changed

- The menu looks slightly different with the separator on (a line between the two blocks). Set `menu.separator` to `false` to keep the 1.0 look.
- Run `php artisan filament:assets` after updating to get the separator line.

## 1.0.1 - 2026-11-14

Bug fixes and tests. No API changes.

### Fixed

- With a relation page class you wrote, a relation whose URL equals one of the resource's own pages (for example `relationRoutes()` spread after `'timeline' => ...::route('/{record}/timeline')`) replaced that page. The resource's page now always wins and that relation has no page. Generated relation pages were not affected.
- Relation managers' badges are computed once per request, even when the breadcrumb menu and the record sub-navigation both show them.
- A menu preset pointing to a missing view now says which config option to check, instead of a generic "View not found".

### Changed

- `HasRelationNavigator` assumes the page has a record, as documented. On a page without a record it did nothing before; now it fails like any other record method.

### Internal

- Tests for nested resources, clusters, tenancy and SPA mode, and browser tests for the searchable menu.
- PHPStan (Larastan) at level 8 in CI.

## 1.0.0 - 2026-11-14

First public release.

- Every relation manager of a resource can have its own page at `/{record}/{slug}`, rendering the relation manager you already have: same table, actions, badges and authorization.
- Opt in with `HasRelationPages` on the resource or `InteractsWithRelationPage` on a relation manager. No page class to write: the routes join the resource's own route group and the page class is generated on demand. Works with `route:cache`.
- Optional page class of your own (`make:filament-relation-navigator`) to customise the relation pages.
- Header above the relation (`relationPageHeader()`), header actions and header/footer widgets, from the resource or the relation manager.
- Breadcrumb menu with the record pages that use `HasRelationNavigator` and every relation page the user can see.
- Menu presets `simple` and `github`, optional search, badges, presets of your own (`make:filament-relation-navigator-preset`) and publishable views.
- Native record sub-navigation from the same items.
- English and Italian translations.
- Filament 4 and 5, Laravel 12 and 13, PHP 8.2 to 8.5.
