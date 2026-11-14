# Changelog

All notable changes to `filament-relation-navigator` are documented here.

## Unreleased

### Added

- **Deferred badges**: `menu.badges` also takes `'lazy'`. The page then renders without the badge queries, and the menu loads the badges when it opens (one request, a spinner meanwhile). New page methods `hasDeferredRelationNavigatorBadges()` and `getRelationNavigatorBadges()`, and `$hasDeferredBadges` for preset views. If you published the package views, update `menu/partials/list.blade.php` before turning it on, or the menu shows no badges.

- **Previous and next record** buttons before the header actions of the pages with the menu (config `record_navigation`, default `false`). They open the neighbour by key on the same page (view, edit, a relation page), or on its first page in the menu when it cannot open that one, and are disabled at the ends. They use the resource's own query, so tenancy and your scopes apply. Per page: `hasRelationNavigatorRecordNavigation()`, `getRelationNavigatorRecordNavigationQuery()`, `getRelationNavigatorRecordNeighbours()`. Nested resources are not supported yet: they show no buttons.

- **"Open as page"** link above the table of every relation manager that has its own page, on the view and edit pages (config `relation_manager_link`, default `true`). It links to the relation group's page for a manager inside a group, and tells apart two entries of the same manager (`PostsRelationManager::make([...])`) by their properties. `RelationNavigator::getRelationManagerPageUrl()` returns the same URL for your own buttons.

### Changed

- **The default menu is now the `github` preset with the search field** (`menu.preset` = `github`, `menu.searchable` = `true`): it is the one with the most features. Apps that published the config keep what they set. To keep the menu of 1.2 and earlier, set `'preset' => 'simple'` and `'searchable' => false` in `config/filament-relation-navigator.php`, or override `getRelationNavigatorPreset()` and `isRelationNavigatorSearchable()` on the page.

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
