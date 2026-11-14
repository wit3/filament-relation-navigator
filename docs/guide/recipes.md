# Recipes

Short answers to common setups. Each one uses only the public API.

## A page for some relations only

Leave the resource alone and opt in on the relation managers you want:

```php
use Wit3\FilamentRelationNavigator\Concerns\InteractsWithRelationPage;

class OrdersRelationManager extends RelationManager
{
    use InteractsWithRelationPage;
}
```

## Pages instead of tabs

Keep the relations on their own pages and drop the tabs from the view page. Old links to tabs (`?relation=orders`) land on the pages:

```php
// ViewCustomer
public function getRelationManagers(): array
{
    return [];
}
```

```php
// config/filament-relation-navigator.php
'redirect_relation_tabs' => true,
```

The menu, "Open as page" and global search keep listing the relation pages: they read the resource's `getRelations()`.

## The record's details above every relation

Reuse the resource's infolist, or a piece of it:

```php
// CustomerResource
public static function relationPageHeader(Schema $schema): Schema
{
    return static::infolist($schema);
}
```

## A resource without a view page

Relation pages work with a list page only. Link them from the table:

```php
Action::make('orders')
    ->url(fn (Customer $record): string => CustomerResource::getUrl('relations.orders', ['record' => $record]))
```

They need the record's `view` policy, since there is no view page to stand for.

## A view page under another key

`'details' => ViewCustomer::route('/{record}')` is found by its class. With a relation page class of your own, you can also name it: `protected static ?string $contextPage = 'details';`.

## Link to a relation page from anywhere

```php
CustomerResource::getUrl('relations.orders', ['record' => $customer]);
```

Inside a tenant, a cluster or a nested resource, Filament fills the missing parameters from the current request, as for any resource URL.

## A quiet menu

The menu of 1.2: the breadcrumb opens a plain list, no search, no badges, no shortcut.

```php
'menu' => [
    'preset' => 'simple',
    'searchable' => false,
    'badges' => false,
    'shortcut' => null,
],
```

## Only for staff

Hide a relation page per record, or for some users, on the resource:

```php
public static function canAccessRelationPage(Model $record, string $slug): bool
{
    return $slug !== 'audit-logs' || auth()->user()->isStaff();
}
```

## Several panels or tenants

Nothing to do: the routes are registered for every panel that has the resource, inside its tenant and cluster, and previous/next remember one list per panel, tenant and resource.
