# Recetas

Respuestas cortas para los casos más comunes. Cada una usa solo la API pública.

## Una página solo para algunas relaciones

Deja el recurso como está y activa las páginas en los relation managers que quieras:

```php
use Wit3\FilamentRelationNavigator\Concerns\InteractsWithRelationPage;

class OrdersRelationManager extends RelationManager
{
    use InteractsWithRelationPage;
}
```

## Páginas en lugar de pestañas

Mantén las relaciones en sus propias páginas y quita las pestañas de la página de vista. Los enlaces antiguos a las pestañas (`?relation=orders`) llevan a las páginas:

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

El menú, «Abrir como página» y la búsqueda global siguen mostrando las páginas de relación: leen el `getRelations()` del recurso.

## Los detalles del registro encima de cada relación

Reutiliza el infolist del recurso, o una parte:

```php
// CustomerResource
public static function relationPageHeader(Schema $schema): Schema
{
    return static::infolist($schema);
}
```

## Un recurso sin página de vista

Las páginas de relación funcionan también con solo la página de lista. Enlázalas desde la tabla:

```php
Action::make('orders')
    ->url(fn (Customer $record): string => CustomerResource::getUrl('relations.orders', ['record' => $record]))
```

Necesitan la policy `view` del registro, porque no hay una página de vista que representar.

## Una página de vista con otra clave

`'details' => ViewCustomer::route('/{record}')` se encuentra por su clase. Con una clase de página de relación propia, también puedes indicarla: `protected static ?string $contextPage = 'details';`.

## Enlazar a una página de relación desde cualquier sitio

```php
CustomerResource::getUrl('relations.orders', ['record' => $customer]);
```

Dentro de un tenant, un cluster o un recurso anidado, Filament completa los parámetros que faltan a partir de la petición actual, como con cualquier URL de un recurso.

## Un menú sencillo

El menú de la 1.2: el breadcrumb abre una lista simple, sin búsqueda, sin badges y sin atajo.

```php
'menu' => [
    'preset' => 'simple',
    'searchable' => false,
    'badges' => false,
    'shortcut' => null,
],
```

## Solo para el personal

Oculta una página de relación por registro, o para algunos usuarios, en el recurso:

```php
public static function canAccessRelationPage(Model $record, string $slug): bool
{
    return $slug !== 'audit-logs' || auth()->user()->isStaff();
}
```

## Varios paneles o tenants

No hay nada que hacer: las rutas se registran para cada panel que tiene el recurso, dentro de su tenant y su cluster, y anterior/siguiente recuerdan una lista por panel, tenant y recurso.
