# Filament Relation Navigator

Give every relation manager of a Filament resource its own page, and switch
between them from the breadcrumb.

- `/users/510?relation=homes` becomes **`/users/510/homes`**: a real route,
  linkable and bookmarkable, with its own browser history.
- The page renders the **relation manager you already have**: same table,
  filters, actions, badges and authorization. Nothing is duplicated.
- The last breadcrumb ("View", "Edit", "Homes") opens a **menu** with the
  record's pages and every relation page the user can see.

Works with Filament **v4** and **v5** (Livewire 3 and 4, Laravel 11–13).
Tested on Filament 4.16 and 5.11.

## Installation

```bash
composer require wit3/filament-relation-navigator
php artisan filament:assets
```

## Usage

### 1. Opt in

On the resource, for every relation manager:

```php
use Wit3\FilamentRelationNavigator\Concerns\HasRelationPages;

class UserResource extends Resource
{
    use HasRelationPages;
}
```

or only on the relation managers you want:

```php
use Wit3\FilamentRelationNavigator\Concerns\InteractsWithRelationPage;

class HomesRelationManager extends RelationManager
{
    use InteractsWithRelationPage;
}
```

That's it: every opted-in relation has a page at `/{record}/{slug}`, named
`relations.{slug}`, in every panel that has the resource.

```php
UserResource::getUrl('relations.homes', ['record' => $user]);
```

There is no page class to write and nothing to add to `getPages()` or to the
panel provider. The package registers the routes inside the resource's own
route group (slug, cluster, parent resource, tenant) and writes the page
class on demand in `storage/framework/cache`, the way Laravel's real-time
facades do. It works with `route:cache`.

### 2. Add the breadcrumb menu to the view / edit pages

```php
use Wit3\FilamentRelationNavigator\Concerns\HasRelationNavigator;

class ViewUser extends ViewRecord
{
    use HasRelationNavigator;
}
```

If the page already builds its own breadcrumbs, pass them through
`withRelationNavigator()`:

```php
public function getBreadcrumbs(): array
{
    return $this->withRelationNavigator([
        UserResource::getUrl('index') => 'Users',
        $this->getBreadcrumb(),
    ]);
}
```

The relation pages have the menu already.

### Customising the relation pages (optional)

To change the title, the breadcrumbs or the header actions of the relation
pages, write the page class yourself and register it. The package then uses
yours instead of generating one:

```bash
php artisan make:filament-relation-navigator UserResource
```

```php
public static function getPages(): array
{
    return [
        'index' => ListUsers::route('/'),
        'view' => ViewUser::route('/{record}'),
        ...UserRelationPage::relationRoutes(),
    ];
}
```

With a page class you wrote, every relation gets a page unless it opts out.

## Header, actions and widgets

Above the relation, a relation page can show part of the record's view, header
actions and widgets. Define them on the resource for every relation page, or
on a relation manager for its own page:

```php
// Part of the view: the whole infolist (static::infolist($schema)) or a piece of it.
public static function relationPageHeader(Schema $schema): Schema
{
    return $schema->components([
        UserCard::make(),
    ]);
}

public static function getRelationPageHeaderActions(): array
{
    return [
        Action::make('clearCache')->action(fn (User $record) => $record->clearCache()),
    ];
}

public static function getRelationPageHeaderWidgets(): array
{
    return [UserStats::class]; // receives the record as $record
}

public static function getRelationPageFooterWidgets(): array
{
    return [];
}
```

The header of a relation manager replaces the resource's one; actions and
widgets of both are shown, the resource's first. Header actions act on the
record the relation belongs to. Pieces that only exist inside the view page
class (its own methods and modals) are not reachable from here: move them to
the resource first.

## Slugs

| `getRelations()` entry                               | Slug                  |
| ---------------------------------------------------- | --------------------- |
| `'homes' => HomesRelationManager::class`             | `homes`               |
| `InvoiceDocumentsRelationManager::class` (no key)    | `invoice-documents`   |
| `RelationGroup::make('Activity', [...])` (no key)    | `activity`            |

A relation manager can choose its own slug with
`getRelationPageSlug()` (see below).

## Choosing which relations get a page

| Setup                                      | Relations with a page                        |
| ------------------------------------------ | -------------------------------------------- |
| `HasRelationPages` on the resource         | all, minus the ones that opt out             |
| `InteractsWithRelationPage` on a manager   | only the managers that opt in                |
| A page class you wrote in `getPages()`     | all, minus the ones that opt out             |

A relation manager shared by two resources opts in for both: use the
resource's `hasRelationPage()` below to leave one out. A slug equal to one
of the resource's own page keys (`edit`, `view`...) is skipped.

On a page class you wrote:

```php
class UserRelationPage extends RelationPage
{
    protected static string $resource = UserResource::class;

    protected static ?array $relations = ['homes', 'invoices'];   // only these

    protected static array $excludedRelations = ['cache-clears']; // never these
}
```

On the resource:

```php
public static function hasRelationPage(string $slug): bool
{
    return $slug !== 'cache-clears';
}
```

On a relation manager:

```php
use Wit3\FilamentRelationNavigator\Concerns\InteractsWithRelationPage;

class CacheClearsRelationManager extends RelationManager
{
    use InteractsWithRelationPage;

    protected static bool $hasRelationPage = false;

    protected static ?string $relationPageSlug = 'cache';
}
```

## How relation managers see the page

The relation manager receives the resource's **view** page as `$pageClass`
(or the edit page when there is no view page), so `isReadOnly()`,
`canViewForRecord()`, `getBadge()` and every other check behave exactly as in
the tab. Change it with `protected static ?string $contextPage = 'edit';`.

Access to a relation page requires both the context page's `canAccess()` and
the manager's `canViewForRecord()`. An unknown slug is a 404.

## Badges

The menu shows the relation managers' badges, which run the same queries as
the tab badges. To hide them on a page:

```php
public function hasRelationNavigatorBadges(): bool
{
    return false;
}
```

## Native record sub-navigation

The same items can feed Filament's record sub-navigation:

```php
public static function getRecordSubNavigation(Page $page): array
{
    return RelationNavigator::getRecordSubNavigation($page);
}
```

## Testing

```bash
composer test
```
