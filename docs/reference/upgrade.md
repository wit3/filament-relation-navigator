# Upgrade guide

The package follows [semantic versioning](https://semver.org): minor and patch releases never break your code. This page lists what you may want to look at when you update. The [changelog](/reference/changelog) has every change.

```bash
composer update wit3/filament-relation-navigator
php artisan filament:assets
```

## To 1.5

Nothing to change. Two behaviours are different:

- **Previous and next follow the list you came from** (sort, filters, search, tab) instead of the key order, when record navigation is on. To keep the key order: `'record_navigation_follows_list' => false`.
- **"Open as page"** opens a relation manager's own page before the page of a relation group it also belongs to.

If you published the package views, publish `menu/partials/list.blade.php` again: relation managers with a lazy tab badge now load it in the menu too.

## To 1.4

Nothing to change.

- The **"Open as page"** link is now an action in the header of the relation manager's table instead of a link above it. If you want it off somewhere, see [Open as page](/guide/open-as-page): config, resource or relation manager.
- `RelationNavigator::renderRelationManagerLink()` is deprecated: the package no longer uses it. It still works for a render hook of your own, and will be removed in 2.0.
- Every menu option can now be set on a resource too: see [Page, resource or config](/guide/menu-presets#page-resource-or-config).

## To 1.3

::: danger Security fix
Versions 1.0.0 to 1.2.1 did not apply the record's `view` policy on relation pages ([GHSA-v3mh-w7qp-37gc](https://github.com/wit3/filament-relation-navigator/security/advisories/GHSA-v3mh-w7qp-37gc)). If you use policies on your records, update.
:::

- **The default menu is now the `github` preset with the search field.** If you published the config, you keep what you set. To keep the menu of 1.2:

  ```php
  // config/filament-relation-navigator.php
  'menu' => [
      'preset' => 'simple',
      'searchable' => false,
  ],
  ```

- The menu no longer lists the edit page to users who cannot edit the record.
- New options, all with defaults in the code: a config published from 1.2 keeps working. Add them to your config when you want to change them: `relation_manager_link`, `record_navigation`, `redirect_relation_tabs`, and `'lazy'` for `menu.badges`.
- If you published the package views and want `'badges' => 'lazy'`, publish `menu/partials/list.blade.php` again.

## To 1.2

- `HasRelationNavigator` now declares `getRecord()` and `hasRecord()`: a page without a record fails as soon as its class is loaded, instead of later. View, edit and pages using `InteractsWithRecord` already have them.
