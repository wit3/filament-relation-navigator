---
name: relation-navigator-development
description: Builds features with wit3/filament-relation-navigator in Filament 4/5 panels. Use when giving relation managers their own pages (/customers/1/orders), adding the breadcrumb menu to record pages, turning options on or off per page, resource or config, adding a header, actions or widgets to relation pages, or writing a custom menu preset.
license: MIT
metadata:
  author: wit3
---

# Relation Navigator Development

## Overview

`wit3/filament-relation-navigator` gives every relation manager of a Filament resource its own page and route, and turns the last breadcrumb of record pages into a menu of the record's pages. Integration is through traits: there are no page classes to generate and no routes to register.

Full documentation: https://wit3.github.io/filament-relation-navigator/ (plain text for agents: https://wit3.github.io/filament-relation-navigator/llms-full.txt).

## Give relation managers their own pages

All relation managers of a resource:

```php
use Wit3\FilamentRelationNavigator\Concerns\HasRelationPages;

class CustomerResource extends Resource
{
    use HasRelationPages;
}
```

Only some relation managers: add `use InteractsWithRelationPage;` to each of them instead.

Rules:

- Pages live at `{record URL}/{slug}`. The slug is the key in `getRelations()`, else the kebab-cased relationship name, else the group label. Override with `protected static ?string $relationPageSlug = 'tickets';` on the relation manager.
- Route names: `{resource route base}.relations.{slug}`. Build URLs with `CustomerResource::getUrl('relations.orders', ['record' => $customer])`.
- Leave one out: `protected static bool $hasRelationPage = false;` on the relation manager, or `public static function hasRelationPage(string $slug): bool` on the resource.
- Do not add these pages to `getPages()`, do not register routes, do not run a generator. Tenancy, clusters, nested resources and resource configurations work as they are.
- The relation manager on its page receives the view page as `$pageClass` (the edit page if there is none). Its checks (`isReadOnly()`, `canViewForRecord()`, badges) behave as in the tab: never branch on the relation page class.
- A relation page requires what the view page requires (its `canAccess()` and the record's `view` policy) plus the manager's `canViewForRecord()`.

## Add the breadcrumb menu

```php
use Wit3\FilamentRelationNavigator\Concerns\HasRelationNavigator;

class ViewCustomer extends ViewRecord
{
    use HasRelationNavigator;
}
```

- A record page (view, edit, custom) is listed in the menu only when it uses the trait.
- A page that already overrides `getBreadcrumbs()` returns `$this->withRelationNavigator([...])`.
- A custom page that uses `InteractsWithRecord` itself resolves the conflict:

```php
use HasRelationNavigator, InteractsWithRecord {
    HasRelationNavigator::getBreadcrumbs insteadof InteractsWithRecord;
    InteractsWithRecord::getBreadcrumbs as getRecordBreadcrumbs;
}
```

## Turn options on or off

Each option is read from the page method, then from a static method with the same name on the resource, then from `config/filament-relation-navigator.php`. Use the narrowest level that does the job.

| Method | Config | Default |
|---|---|---|
| `getRelationNavigatorPreset(): string` | `menu.preset` | `github` |
| `isRelationNavigatorSearchable(): bool` | `menu.searchable` | `true` |
| `hasRelationNavigatorBadges(): bool` | `menu.badges` | `true` |
| `hasDeferredRelationNavigatorBadges(): bool` | `menu.badges` = `'lazy'` | `false` |
| `getRelationNavigatorShortcut(): ?string` | `menu.shortcut` | `alt+shift+r` |
| `hasRelationNavigatorMobileMenu(): bool` | `menu.mobile` | `true` |
| `hasRelationNavigatorRecordNavigation(): bool` | `record_navigation` | `false` |
| `shouldRelationNavigatorRecordNavigationFollowList(): bool` (previous/next follow the list's sort and filters) | `record_navigation_follows_list` | `true` |
| `shouldRedirectRelationNavigatorTabs(): bool` | `redirect_relation_tabs` | `false` |

```php
// Resource: previous/next buttons on all its pages.
public static function hasRelationNavigatorRecordNavigation(): bool
{
    return true;
}
```

"Open as page" action in relation manager tables: config `relation_manager_link`, resource `public static function hasRelationPageLinks(): bool`, relation manager `protected static bool $hasRelationPageLink = false;` (with `InteractsWithRelationPage`) or `public static function hasRelationPageLink(): bool`. All three must allow it.

## Header, actions and widgets on relation pages

Define on the resource (every relation page) or on a relation manager (its page only; its header replaces the resource's, actions and widgets add up):

```php
public static function relationPageHeader(Schema $schema): Schema
{
    return $schema->components([CustomerCard::make()]);
}

public static function getRelationPageHeaderActions(): array
{
    return [Action::make('email')->url(fn (Customer $record): string => "mailto:{$record->email}")];
}

public static function getRelationPageHeaderWidgets(): array
{
    return [CustomerStats::class]; // receives $record
}
```

They act on the owner record. Methods that exist only on the view page class are not reachable: move shared pieces to the resource or a class.

## Title, subheading and breadcrumb of one relation page

Static methods on the relation manager, given the owner record; return `null` to keep the default. The menu and tabs keep the relation manager's title.

```php
public static function getRelationPageTitle(Model $ownerRecord): string|Htmlable|null
{
    return "Orders of {$ownerRecord->name}";
}

public static function getRelationPageSubheading(Model $ownerRecord): string|Htmlable|null;
public static function getRelationPageBreadcrumb(Model $ownerRecord): ?string;
```

## Global search links

Off by default. Config `global_search_actions` (`true`, or the most links per result), resource `public static function hasRelationPageGlobalSearchActions(): bool`, relation manager `protected static bool $hasRelationPageGlobalSearchAction = false;`. `HasRelationPages` adds them to `getGlobalSearchResultActions()`; a resource that defines that method itself spreads `...RelationNavigator::getGlobalSearchResultActions(static::class, $record)` into it.

## Previous and next record

Off by default (`record_navigation`). When on, the buttons follow the list the user came from (sort, filters, search, tab; `record_navigation_follows_list`, on by default) and walk by key for records not in it. The list only remembers its records when record navigation is on in the config or on the resource. Nested resources are not supported.

## Custom menu preset

```bash
php artisan make:filament-relation-navigator-preset compact --from=github
```

Register it under `menu.presets` and select it with `menu.preset`. In preset views never put Blade `@if` inside an HTML tag's attributes (Livewire adds comment markers there): build attribute strings in PHP. Reuse `filament-relation-navigator::menu.partials.list` and `menu.partials.trigger-attributes`.

## Testing

- Relation pages are real routes: `$this->get(CustomerResource::getUrl('relations.orders', ['record' => $customer]))->assertOk()`.
- Relation managers are lazy by default: in a full-page request their table is not rendered. Test them with `Livewire::test(OrdersRelationManager::class, ['ownerRecord' => $customer, 'pageClass' => ViewCustomer::class])`.
- After changing the package CSS or updating, run `php artisan filament:assets`.
