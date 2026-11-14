# Configuration

Publish the config file to change the defaults for every resource:

```bash
php artisan vendor:publish --tag=filament-relation-navigator-config
```

A published config keeps working across minor versions: every key has a default in the code, so new keys only need adding when you want to change them.

## Options

Most options can also be set **per resource** (a static method on the resource) or **per page or relation manager**:

- **Menu options** (`menu.*` with a method, record navigation, tab redirect): the page wins, then the resource, then the config.
- **"Open as page"** and **a relation manager's menu badge**: shown only when every level allows it; any level can turn it off.
- **Global search links**: the resource's method replaces the config for that resource; a relation manager can only leave itself out.
- **The list remembered for previous and next**: decided by the resource or the config only, since the list page cannot ask a record page.
- `menu.presets`, `menu.search_from`, `menu.max_height` and `menu.separator` are config only.

| Key | Default | What it does | Other levels |
|---|---|---|---|
| `menu.preset` | `'github'` | The menu's look: `github`, `simple`, a preset of yours or a view name | `getRelationNavigatorPreset()` |
| `menu.presets` | `simple`, `github` | Names and views of the presets | |
| `menu.searchable` | `true` | A search field on top of the list | `isRelationNavigatorSearchable()` |
| `menu.search_from` | `0` | Show the search field from this many items | |
| `menu.badges` | `'lazy'` | `'lazy'`: badges load when the menu opens; `true`: with the page; `false`: none | `hasRelationNavigatorBadges()`, `hasDeferredRelationNavigatorBadges()`; relation manager: `hasRelationNavigatorBadge()` |
| `menu.max_height` | `'24rem'` | Height of the list before it scrolls (a CSS length) | |
| `menu.separator` | `true` | Record pages and relation pages in two blocks | |
| `menu.mobile` | `true` | The menu above the heading on small screens | `hasRelationNavigatorMobileMenu()` |
| `menu.shortcut` | `'alt+shift+r'` | Keyboard shortcut that opens the menu; `null`: none | `getRelationNavigatorShortcut()` |
| `relation_manager_link` | `true` | The "Open as page" action on relation manager tables | resource `hasRelationPageLinks()`, relation manager `hasRelationPageLink()` |
| `relation_manager_action` | link with an arrow | Look of that action: `style`, `label`, `icon`, `icon_position`, `color`, `size`, `new_tab` | `configureRelationPageActionUsing()`, `configureRelationPageAction()` |
| `global_search_actions` | `false` | Links to relation pages under global search results: `true` or the most per result | resource `hasRelationPageGlobalSearchActions()`, relation manager `hasRelationPageGlobalSearchAction()` |
| `record_navigation` | `false` | Previous and next record buttons | `hasRelationNavigatorRecordNavigation()` |
| `record_navigation_follows_list` | `true` | Previous and next follow the list the user came from | `shouldRelationNavigatorRecordNavigationFollowList()` |
| `redirect_relation_tabs` | `false` | `?relation=x` on the view page opens the relation page | `shouldRedirectRelationNavigatorTabs()` |

Page methods are instance methods on a record page using `HasRelationNavigator`; on a resource the same method is static and applies to all its pages. See [Page, resource or config](/guide/menu-presets#page-resource-or-config).

## The whole file

As shipped with the current version:

<<< ../../config/filament-relation-navigator.php
