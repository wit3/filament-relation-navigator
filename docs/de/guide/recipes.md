# Rezepte

Kurze Antworten auf häufige Setups. Jede nutzt nur die öffentliche API.

## Eine Seite nur für einige Relationen

Lass die Resource, wie sie ist, und aktiviere die Seiten an den Relation Managern, die du willst:

```php
use Wit3\FilamentRelationNavigator\Concerns\InteractsWithRelationPage;

class OrdersRelationManager extends RelationManager
{
    use InteractsWithRelationPage;
}
```

## Seiten statt Tabs

Behalte die Relationen auf ihren eigenen Seiten und entferne die Tabs von der Ansichtsseite. Alte Links auf Tabs (`?relation=orders`) landen auf den Seiten:

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

Das Menü, „Als Seite öffnen“ und die globale Suche führen die Relationsseiten weiter auf: sie lesen `getRelations()` der Resource.

## Die Details des Datensatzes über jeder Relation

Verwende die Infolist der Resource wieder, oder einen Teil davon:

```php
// CustomerResource
public static function relationPageHeader(Schema $schema): Schema
{
    return static::infolist($schema);
}
```

## Eine Resource ohne Ansichtsseite

Relationsseiten funktionieren auch nur mit einer Listenseite. Verlinke sie aus der Tabelle:

```php
Action::make('orders')
    ->url(fn (Customer $record): string => CustomerResource::getUrl('relations.orders', ['record' => $record]))
```

Sie brauchen die `view`-Policy des Datensatzes, da es keine Ansichtsseite gibt, für die sie stehen.

## Eine Ansichtsseite unter einem anderen Schlüssel

`'details' => ViewCustomer::route('/{record}')` wird über ihre Klasse gefunden. Mit einer eigenen Relationsseitenklasse kannst du sie auch benennen: `protected static ?string $contextPage = 'details';`.

## Von überall auf eine Relationsseite verlinken

```php
CustomerResource::getUrl('relations.orders', ['record' => $customer]);
```

Innerhalb eines Tenants, eines Clusters oder einer verschachtelten Resource ergänzt Filament die fehlenden Parameter aus dem aktuellen Request, wie bei jeder Resource-URL.

## Ein schlichtes Menü

Das Menü von 1.2: die Breadcrumb öffnet eine einfache Liste, ohne Suche, ohne Badges, ohne Tastenkürzel.

```php
'menu' => [
    'preset' => 'simple',
    'searchable' => false,
    'badges' => false,
    'shortcut' => null,
],
```

## Nur für das Team

Verstecke eine Relationsseite pro Datensatz oder für bestimmte Benutzer, auf der Resource:

```php
public static function canAccessRelationPage(Model $record, string $slug): bool
{
    return $slug !== 'audit-logs' || auth()->user()->isStaff();
}
```

## Mehrere Panels oder Tenants

Nichts zu tun: die Routen werden für jedes Panel registriert, das die Resource hat, innerhalb seines Tenants und Clusters, und vorheriger/nächster Datensatz merken sich eine Liste pro Panel, Tenant und Resource.
