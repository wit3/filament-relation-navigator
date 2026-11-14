# Changelog

All notable changes to `filament-relation-navigator` are documented here.

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
