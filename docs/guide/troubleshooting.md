# Troubleshooting

## The menu does not show

- The page must use `HasRelationNavigator`. Relation pages always have it.
- The menu needs at least two items: the page itself and one more.
- A page that builds its own breadcrumbs must return `$this->withRelationNavigator([...])`.
- Below 40rem Filament hides the breadcrumbs: the menu moves above the heading (`menu.mobile`).

## The menu looks wrong or the caret is misplaced

Publish the plugin's stylesheet again, after every update:

```bash
php artisan filament:assets
```

## A relation page is a 404

- The relation manager has no page: the resource needs `HasRelationPages`, or the relation manager `InteractsWithRelationPage`, and it must not opt out.
- Its slug equals a page key of the resource (`edit`, `view`...): such slugs are skipped. Pick another with `$relationPageSlug`.
- With cached routes, cache them again after a deploy: `php artisan route:cache`.
- The policy denies the record "as not found" (`Response::denyAsNotFound()`): that is a 404 on purpose.

## A relation page is a 403

A relation page needs the view page's `canAccess()`, the record's `view` policy (`update` for an edit page), the relation manager's `canViewForRecord()` and, if you defined it, the resource's `canAccessRelationPage()`. Check them in that order.

## The badges do not show

- `menu.badges` is `false`, the page or resource turns them off, or the relation manager does (`hasRelationNavigatorBadge()`).
- By default they load when the menu opens, with a spinner. If the spinner never stops, look at the browser console for a failed Livewire request.
- Views published before 1.3 cannot load badges later: they get them with the page instead.

## The shortcut does nothing

- It does nothing while you type in a field.
- A shortcut of the panel's global search (`globalSearchKeyBindings()`) is never taken: choose another with `menu.shortcut`.
- A browser extension or the operating system may use the same keys.

## "Open as page" is missing

- `relation_manager_link` is `false`, or the resource or relation manager turns it off.
- The relation manager has no page of its own, or you are already on it.
- A table with a custom `->header()` view has no place for header actions.

## Previous and next are missing

- They are off by default: `record_navigation`.
- They hide when there is no other record (on a nested resource, no other record of the same parent).
- To follow the list's order, the list must have been opened in this session, with record navigation on in the config or on the resource.

## "A directory that only this user can write"

The storage is read-only, so the generated page classes go to the system temporary directory, and that directory belongs to someone else. Make `storage/framework/cache` writable, remove the directory named in the message, or choose one in a service provider:

```php
GeneratedRelationPages::useCachePath(storage_path('app/relation-pages'));
```

## Still stuck

Ask in [GitHub Discussions](https://github.com/wit3/filament-relation-navigator/discussions), or open an [issue](https://github.com/wit3/filament-relation-navigator/issues) with your Filament version and a few lines that reproduce it.
