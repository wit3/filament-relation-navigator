# Changelog

All notable changes to `filament-relation-navigator` are documented here.

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
