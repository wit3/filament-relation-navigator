# API reference

Everything below is public API, covered by [semantic versioning](/reference/upgrade): it does not change in a breaking way within 1.x. Anything not listed here is internal.

## Traits

| Trait | Use it on | What it does |
|---|---|---|
| `Concerns\HasRelationPages` | a resource | Every relation manager gets its own page |
| `Concerns\InteractsWithRelationPage` | a relation manager | This relation manager gets a page; reads the optional static properties below |
| `Concerns\HasRelationNavigator` | a record page (view, edit, custom) | The breadcrumb menu, and the page is listed in it |

All in the `Wit3\FilamentRelationNavigator` namespace.

## On a resource

All static, all optional.

| Method | Purpose |
|---|---|
| `hasRelationPage(string $slug): bool` | Leave a relation out |
| `canAccessRelationPage(Model $record, string $slug): bool` | Veto a relation page for a record (pages, menu, global search, redirect) |
| `relationPageHeader(Schema $schema): Schema` | Header above the relation on every relation page |
| `getRelationPageHeaderActions(): array` | Header actions of the relation pages |
| `getRelationPageHeaderWidgets(): array`, `getRelationPageFooterWidgets(): array` | Widgets of the relation pages (they receive `$record`) |
| `hasRelationPageLinks(): bool` | "Open as page" on its relation managers |
| `configureRelationPageAction(Action $action, RelationManager $manager): Action` | Change that action for this resource |
| `hasRelationPageGlobalSearchActions(): bool` | Global search links for this resource |
| any [option method](/reference/configuration#options), static | The option for all the resource's pages |

## On a relation manager

All static, all optional. Properties need `InteractsWithRelationPage`; methods work without it.

| Property or method | Purpose |
|---|---|
| `$hasRelationPage`, `hasRelationPage(): bool` | A page for this relation manager or not |
| `$relationPageSlug`, `getRelationPageSlug(): ?string` | The slug of its page |
| `$hasRelationPageLink`, `hasRelationPageLink(): bool` | "Open as page" on its table |
| `$hasRelationPageGlobalSearchAction`, `hasRelationPageGlobalSearchAction(): bool` | Its link under global search results |
| `$hasRelationNavigatorBadge`, `hasRelationNavigatorBadge(): bool` | Its badge in the menu (the tab keeps its own) |
| `getRelationPageTitle(Model $ownerRecord)` | Title of its page; `null` keeps the default |
| `getRelationPageSubheading(Model $ownerRecord)` | Subheading of its page |
| `getRelationPageBreadcrumb(Model $ownerRecord): ?string` | Last breadcrumb of its page |
| `relationPageHeader()`, `getRelationPageHeaderActions()`, `getRelationPageHeaderWidgets()`, `getRelationPageFooterWidgets()` | Like on the resource, for its page only |
| `configureRelationPageAction(Action $action, RelationManager $manager): Action` | Change its "Open as page" action |

Filament's own `isBadgeLazyLoaded()` (or `$isBadgeLazyLoaded`) also makes its menu badge lazy.

## On a record page (`HasRelationNavigator`)

Override any of them on the page:

| Method | Purpose |
|---|---|
| the [option methods](/reference/configuration#options) | Options for this page |
| `getRelationNavigatorRecordNavigationQuery(): ?Builder` | The records previous and next walk through |
| `withRelationNavigator(array $breadcrumbs): array` | Add the menu to breadcrumbs you build yourself |

## A relation page class of your own

Extend `Pages\RelationPage` (or run `php artisan make:filament-relation-navigator CustomerResource`), and spread `...CustomerRelationPage::relationRoutes()` into `getPages()`.

| Member | Purpose |
|---|---|
| `protected static ?array $relations` | Only these slugs |
| `protected static array $excludedRelations` | Never these slugs |
| `protected static ?string $contextPage` | Key of the page it stands for (default: the view page, else the edit page) |
| `$title`, `$subheading`, `$breadcrumb`, and every Filament page method | As on any Filament page |

## Helpers

Static methods of `RelationNavigator`:

| Method | Purpose |
|---|---|
| `getItems(string $resource, Model $record, ...)` | The menu items of a record |
| `getRecordSubNavigation(Page $page): array` | The same items as Filament sub-navigation |
| `getRelationManagerPageUrl(RelationManager $manager): ?string` | The page of a relation manager, for buttons of your own |
| `getGlobalSearchResultActions(string $resource, Model $record): array` | Global search links, to merge into your own |
| `configureRelationPageActionUsing(Closure $callback): void` | Change every "Open as page" action |
| `makeRelationPageAction(RelationManager $manager): Action` | Build that action |
| `getRelationPageClass(string $resource): ?string` | The relation page class of a resource |
| `RELATION_PAGE_ACTION` | Name of the "Open as page" action |

`RelationNavigatorItem` (in `$items`): `key`, `label`, `url`, `icon`, `badge`, `badgeColor`, `isActive`, `isBadgeDeferred`, `isRelationPage()`.

## Routes, views and commands

- Route names: `{resource route base}.relations.{slug}`, for `getUrl('relations.orders', [...])`.
- Views: `filament-relation-navigator::menu.github`, `menu.simple`, `menu.mobile`, `menu.partials.list`, `menu.partials.trigger-attributes`. Their variables are listed under [A preset of your own](/guide/menu-presets#a-preset-of-your-own).
- Commands: `make:filament-relation-navigator {resource}`, `make:filament-relation-navigator-preset {name} --from=github|simple`.
- Translations: `filament-relation-navigator::navigator.*`.
