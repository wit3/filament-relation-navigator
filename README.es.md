<img class="filament-hidden" src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/cover.jpg" alt="Relation Navigator para Filament">

# Relation Navigator para Filament

<p class="filament-hidden">
    <a href="https://packagist.org/packages/wit3/filament-relation-navigator"><img src="https://img.shields.io/packagist/v/wit3/filament-relation-navigator.svg?style=flat-square" alt="Última versión en Packagist"></a>
    <a href="https://github.com/wit3/filament-relation-navigator/actions/workflows/tests.yml"><img src="https://img.shields.io/github/actions/workflow/status/wit3/filament-relation-navigator/tests.yml?branch=main&label=tests&style=flat-square" alt="Tests"></a>
    <a href="https://packagist.org/packages/wit3/filament-relation-navigator"><img src="https://img.shields.io/packagist/dt/wit3/filament-relation-navigator.svg?style=flat-square" alt="Descargas totales"></a>
    <a href="LICENSE.md"><img src="https://img.shields.io/packagist/l/wit3/filament-relation-navigator.svg?style=flat-square" alt="Licencia"></a>
</p>

> **Documentación:** guías, configuración, guía de actualización y búsqueda en
> **[wit3.github.io/filament-relation-navigator/es](https://wit3.github.io/filament-relation-navigator/es/)**.
>
> **Última versión:** [Novedades](https://wit3.github.io/filament-relation-navigator/es/reference/changelog) [![Latest version on Packagist](https://img.shields.io/packagist/v/wit3/filament-relation-navigator.svg?style=flat-square&label=version)](https://github.com/wit3/filament-relation-navigator/releases/latest)
>
> **En otros idiomas:** la documentación en
> [English](https://wit3.github.io/filament-relation-navigator/) (el original) · [Italiano](https://wit3.github.io/filament-relation-navigator/it/) · [Português](https://wit3.github.io/filament-relation-navigator/pt/) · [Français](https://wit3.github.io/filament-relation-navigator/fr/) · [Deutsch](https://wit3.github.io/filament-relation-navigator/de/) · [中文](https://wit3.github.io/filament-relation-navigator/zh/) · [日本語](https://wit3.github.io/filament-relation-navigator/ja/) · [Русский](https://wit3.github.io/filament-relation-navigator/ru/) · [العربية](https://wit3.github.io/filament-relation-navigator/ar/).
> Cada idioma tiene también su README en este repositorio (`README.md`, `README.it.md`...).

<!-- #region intro -->
Dale a cada relation manager de un recurso de Filament **su propia página**, y
pasa de una a otra desde un **menú en el breadcrumb**.

- `/customers/1?relation=orders` se convierte en **`/customers/1/orders`**: una
  ruta real que puedes enlazar, guardar en marcadores y abrir en una pestaña nueva.
- La página muestra **el relation manager que ya tienes**: la misma tabla,
  filtros, acciones, badges y permisos. Nada se duplica.
- Añade una **cabecera** encima de la relación (la ficha del registro, widgets,
  acciones), tomada del recurso.
- El último elemento del breadcrumb abre un **menú** con las páginas del
  registro y todas las relaciones que el usuario puede ver, con iconos y
  badges. Dos aspectos, búsqueda opcional y navegación con teclado, una vista
  propia y una versión para pantallas pequeñas.
- **Registro anterior y siguiente** en la misma página, una acción **«Abrir
  como página»** en cada tabla de relation manager, y enlaces a las pestañas
  antiguas que llevan a las páginas nuevas: cada uno se activa o desactiva de
  forma global, por recurso o por página.
- **Un trait, ninguna clase de página**: no hay archivos que generar ni nada que
  añadir a `getPages()` o al panel provider.
- **32 idiomas**, incluidos los de derecha a izquierda, con los códigos de
  Filament. La documentación está en 10.

<img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/demo.gif" alt="El menú abierto con Alt Mayús R, filtrado por Invoices, y luego el cliente siguiente en la misma página" width="880">
<!-- #endregion intro -->

## Requisitos

<!-- #region requirements -->
| Paquete  | Versión              |
| -------- | -------------------- |
| PHP      | 8.2+                 |
| Laravel  | 12, 13               |
| Filament | 4.13+, 5.x           |
<!-- #endregion requirements -->

## Instalación

<!-- #region installation -->
```bash
composer require wit3/filament-relation-navigator
php artisan filament:assets
```

Vuelve a ejecutar `filament:assets` después de cada actualización: Filament
copia la hoja de estilos del plugin en `public/`.
<!-- #endregion installation -->

## Inicio rápido

<!-- #region quick-start -->
**1. Dale una página a las relaciones.** Añade el trait al recurso:

```php
use Wit3\FilamentRelationNavigator\Concerns\HasRelationPages;

class CustomerResource extends Resource
{
    use HasRelationPages;

    public static function getRelations(): array
    {
        return [
            'orders' => OrdersRelationManager::class,
            'invoices' => InvoicesRelationManager::class,
            NotesRelationManager::class,
        ];
    }
}
```

Ahora cada relación tiene su página: `/customers/1/orders`,
`/customers/1/invoices`, `/customers/1/notes`.

**2. Añade el menú a la página de vista:**

```php
use Wit3\FilamentRelationNavigator\Concerns\HasRelationNavigator;

class ViewCustomer extends ViewRecord
{
    use HasRelationNavigator;
}
```

Eso es todo. Las páginas de relación ya tienen el menú, y las pestañas de la
página de vista siguen funcionando como antes.

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-light.png" alt="Los pedidos de un cliente en su propia página, con el menú del breadcrumb abierto">
</picture>
<!-- #endregion quick-start -->

## Páginas de relación

<!-- #region relation-pages -->
### Activarlas

Actívalas en el recurso, para todos los relation managers, o solo en los
relation managers que quieras:

```php
use Wit3\FilamentRelationNavigator\Concerns\InteractsWithRelationPage;

class OrdersRelationManager extends RelationManager
{
    use InteractsWithRelationPage;
}
```

| Configuración                               | Relaciones con página               |
| ------------------------------------------- | ----------------------------------- |
| `HasRelationPages` en el recurso            | todas, menos las excluidas          |
| `InteractsWithRelationPage` en un manager   | solo los managers que lo usan       |
| Una clase de página escrita por ti (abajo)  | todas, menos las excluidas          |

Para dejar fuera algunas relaciones:

```php
// En el recurso
public static function hasRelationPage(string $slug): bool
{
    return $slug !== 'notes';
}

// En un relation manager, con InteractsWithRelationPage
protected static bool $hasRelationPage = false;
```

Un relation manager compartido por dos recursos se activa en ambos: usa
`hasRelationPage()` del recurso para dejar fuera uno de ellos.

### URL y slugs

| Entrada de `getRelations()`                     | URL                         |
| ----------------------------------------------- | --------------------------- |
| `'orders' => OrdersRelationManager::class`      | `/customers/1/orders`       |
| `SupportTicketsRelationManager::class` (sin clave) | `/customers/1/support-tickets` |
| `RelationGroup::make('Activity', [...])`        | `/customers/1/activity`     |

Gana el slug propio del relation manager
(`protected static ?string $relationPageSlug = 'tickets';`), después la clave
en `getRelations()`, después el nombre de la relación en kebab-case (o la
etiqueta del grupo: dale una clave a los grupos, porque una etiqueta traducida
cambia con el idioma). Los slugs iguales a una de las claves de página del
recurso (`edit`, `view`...) se omiten, y también una segunda relación con un
slug ya ocupado.

Cada página es una ruta con nombre, `relations.{slug}`:

```php
CustomerResource::getUrl('relations.orders', ['record' => $customer]);
```

### Permisos y comportamiento

El relation manager recibe como `$pageClass` la página de **vista** del
recurso (la de edición si no hay página de vista), así que `isReadOnly()`,
`canViewForRecord()`, `getBadge()` y todas las demás comprobaciones funcionan
exactamente igual que en la pestaña. La página de vista se encuentra por su
clave (`view`, `edit`, o `$contextPage` en una clase de página escrita por ti)
o por su clase.

Una página de relación necesita lo mismo que la página de vista (su
`canAccess()` y la policy `view` del registro; `update` cuando representa la
página de edición) y el `canViewForRecord()` del manager. Sin página de vista
ni de edición, se aplica la policy `view`. Una policy que deniega «como no
encontrado» da un 404, igual que un slug desconocido. El menú, la búsqueda
global y «Abrir como página» solo enlazan las páginas que el usuario puede
abrir.

Las páginas de relación no ejecutan el código propio de la página de vista:
las comprobaciones de su `authorizeAccess()` o un `getRelationManagers()`
filtrado por usuario no se aplican ahí. Ponlas en las policies, en
`canViewForRecord()`, o veta una página de relación por registro en el
recurso:

```php
public static function canAccessRelationPage(Model $record, string $slug): bool
{
    return $slug !== 'audit-logs' || auth()->user()->isAdmin();
}
```
<!-- #endregion relation-pages -->

## Cabecera, acciones y widgets

<!-- #region header-actions-and-widgets -->
Encima de la relación, una página de relación puede mostrar una parte de la
vista del registro, acciones de cabecera y widgets. Defínelos en el recurso
para todas las páginas de relación, o en un relation manager para su propia
página:

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-light.png" alt="Una página de relación con widgets, la ficha del cliente y una acción de cabecera encima de los pedidos" width="880">
</picture>

```php
public static function relationPageHeader(Schema $schema): Schema
{
    // Todo el infolist (static::infolist($schema)) o una parte.
    return $schema->components([
        CustomerCard::make(),
    ]);
}

public static function getRelationPageHeaderActions(): array
{
    return [
        Action::make('email')
            ->url(fn (Customer $record): string => "mailto:{$record->email}"),
    ];
}

public static function getRelationPageHeaderWidgets(): array
{
    return [CustomerStats::class]; // recibe el registro como $record
}

public static function getRelationPageFooterWidgets(): array
{
    return [];
}
```

- La cabecera de un relation manager reemplaza la del recurso; las acciones y
  los widgets de ambos se muestran, primero los del recurso.
- Las acciones de cabecera actúan sobre el registro al que pertenece la
  relación.
- Con una cabecera, el nombre del registro no se repite debajo del título.
- Lo que solo existe dentro de la clase de la página de vista (sus propios
  métodos y modales) no se puede alcanzar desde aquí: muévelo al recurso, a
  una clase de acción o a un trait.
<!-- #endregion header-actions-and-widgets -->

## El menú del breadcrumb

<!-- #region the-breadcrumb-menu -->
`HasRelationNavigator` necesita una página con un registro: `ViewRecord`,
`EditRecord` y las páginas que usan `InteractsWithRecord` ya tienen los dos
métodos que declara, `getRecord()` y `hasRecord()`. En una página sin registro,
PHP lo indica en cuanto se carga la clase.

Una página del registro aparece en el menú si, y solo si, usa
`HasRelationNavigator`: la página de vista, la de edición o cualquier página
personalizada del registro. En esas páginas, el último elemento del breadcrumb
abre el menú. Las páginas de relación aparecen siempre, después de las páginas
del registro, con los iconos y badges de los relation managers, y solo cuando
el usuario puede verlas.

Si la página ya construye su propio breadcrumb, pásalo por
`withRelationNavigator()`:

```php
/**
 * @return array<string | int, string | Htmlable>
 */
public function getBreadcrumbs(): array
{
    return $this->withRelationNavigator([
        CustomerResource::getUrl('index') => 'Customers',
        $this->getBreadcrumb(),
    ]);
}
```

Una página personalizada que usa `InteractsWithRecord` directamente, en lugar
de extender `ViewRecord` o `EditRecord`, tiene dos métodos `getBreadcrumbs()`.
Si no define el suyo, dile a PHP cuál gana:

```php
class CustomerActivity extends Page
{
    use HasRelationNavigator, InteractsWithRecord {
        HasRelationNavigator::getBreadcrumbs insteadof InteractsWithRecord;
        InteractsWithRecord::getBreadcrumbs as getRecordBreadcrumbs;
    }
}
```

El menú usa `$navigationLabel` y `$navigationIcon` de la página, y la muestra
solo cuando `canAccess()` lo permite para el registro.
<!-- #endregion the-breadcrumb-menu -->

## Abrir como página

<!-- #region open-as-page -->
En las páginas de vista y de edición, cada relation manager que tiene su propia
página recibe una acción **Abrir como página** en la cabecera de su tabla, junto
a su título y antes de sus propias acciones. Abre la página propia del relation
manager, o la página de su grupo de relaciones si no tiene una propia. En la
página de un grupo, los managers con página propia también reciben la acción;
en su propia página, un manager nunca se enlaza a sí mismo.

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/open-as-page-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/open-as-page-light.png" alt="La acción Abrir como página en la cabecera de la tabla Orders, en la página de vista" width="880">
</picture>

Desactívala donde lo necesites:

```php
// config/filament-relation-navigator.php: en todas partes
'relation_manager_link' => false,
```

```php
// En un recurso: todos sus relation managers
public static function hasRelationPageLinks(): bool
{
    return false;
}
```

```php
// En un relation manager (con InteractsWithRelationPage)
protected static bool $hasRelationPageLink = false;

// o, sin el trait
public static function hasRelationPageLink(): bool
{
    return false;
}
```

### Su aspecto

Por defecto es un enlace con una flecha. Cámbialo en la config:

```php
'relation_manager_action' => [
    'style' => 'button',        // link, button o icon_button (la etiqueta pasa a ser un tooltip)
    'label' => 'Página completa', // texto o clave de traducción; null: «Abrir como página», traducido
    'icon' => 'heroicon-m-arrow-top-right-on-square', // cualquier icono de Filament, o null
    'icon_position' => 'after', // o before
    'color' => 'gray',
    'size' => null,             // sm, md, lg...
    'new_tab' => false,
],
```

O cambia la propia acción de Filament, para todo excepto su URL y cuándo se
muestra, que decide el paquete. Cada nivel recibe la acción tal como la dejó
el anterior:

```php
// Un service provider: todos los relation managers
RelationNavigator::configureRelationPageActionUsing(
    fn (Action $action, RelationManager $relationManager) => $action->outlined()->badge('nuevo'),
);
```

```php
// Un recurso, o un relation manager: solo ahí
public static function configureRelationPageAction(Action $action): Action
{
    return $action->iconButton()->tooltip('Abrir los pedidos en su propia página');
}
```

`RelationNavigator::getRelationManagerPageUrl($relationManager)` devuelve la
misma URL, para un botón propio.
<!-- #endregion open-as-page -->

## Registro anterior y siguiente

<!-- #region previous-and-next-record -->
Dos botones antes de las acciones de cabecera abren el registro anterior o el
siguiente **en la misma página**: de las facturas de un cliente a las facturas
del cliente siguiente.

<img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/record-navigation.gif" alt="Registro anterior y siguiente: de las facturas de un cliente a las del siguiente, con un tooltip que nombra el registro" width="880">

Un registro que no puede abrir esa página se abre en su primera
página del menú. Están desactivados por defecto, porque añaden algunas
consultas en cada renderizado:

```php
'record_navigation' => true,
```

Siguen **la lista de la que vienes**: su orden, filtros, búsqueda y pestaña,
tal como se ven en pantalla (hasta 100 registros; unos 30 con claves UUID).
Desde el primer o el último registro de esa lista no se avanza más, en lugar
de saltar a otro orden. Un registro que no está en la lista (abierto desde un
enlace) sigue el orden de las claves. Para seguir siempre el orden de las
claves:

```php
'record_navigation_follows_list' => false,
```

La lista recuerda sus registros solo cuando la navegación entre registros está
activa en la config o en el recurso (`hasRelationNavigatorRecordNavigation()`),
porque la página de la lista no puede preguntarle a una página del registro.

Recorren la consulta propia del recurso (`getRecordRouteBindingEloquentQuery()`),
así que se aplican el tenancy y tus scopes, y omiten los registros que el
usuario no puede ver. Por página:

```php
public function hasRelationNavigatorRecordNavigation(): bool
{
    return true;
}

public function getRelationNavigatorRecordNavigationQuery(): ?Builder
{
    return Customer::query()->where('active', true);
}
```

En un **recurso anidado** se quedan dentro del padre: de uno de los pedidos de
un cliente al pedido siguiente del mismo cliente. Ahí la lista es el relation
manager del padre: siguen su orden cuando el recurso anidado es su recurso
relacionado (`protected static ?string $relatedResource`), excepto con el
driver de sesión `cookie`, que solo conserva la lista del padre.
<!-- #endregion previous-and-next-record -->

## Enlaces a las pestañas de los relation managers

<!-- #region links-to-relation-manager-tabs -->
Cuando las páginas de relación reemplazan a las pestañas, los enlaces antiguos
como `/customers/1?relation=orders` pueden abrir `/customers/1/orders`:

```php
'redirect_relation_tabs' => true,
```

Las pestañas sin página de relación, o que el registro no puede ver, se quedan
donde están.
<!-- #endregion links-to-relation-manager-tabs -->

## Búsqueda global

<!-- #region global-search -->
Los resultados de la búsqueda global pueden enlazar directamente a las páginas
de relación del registro: buscas un cliente y saltas a sus pedidos. Desactivado
por defecto, porque ejecuta el `canViewForRecord()` de los relation managers
para cada resultado:

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/global-search-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/global-search-light.png" alt="Un cliente en los resultados de la búsqueda global, con enlaces a sus pedidos, facturas, direcciones y contactos" width="420">
</picture>

```php
// config/filament-relation-navigator.php
'global_search_actions' => 3, // true para todos, o el máximo de enlaces por resultado
```

```php
// En un recurso: activado o desactivado solo para este recurso
public static function hasRelationPageGlobalSearchActions(): bool
{
    return true;
}
```

```php
// En un relation manager (con InteractsWithRelationPage): nunca enlazado
protected static bool $hasRelationPageGlobalSearchAction = false;
```

Los recursos con `HasRelationPages` reciben los enlaces solos. Un recurso que
define `getGlobalSearchResultActions()` por su cuenta, o que solo se activa
desde sus relation managers, los añade así:

```php
public static function getGlobalSearchResultActions(Model $record): array
{
    return [
        // ...tus acciones
        ...RelationNavigator::getGlobalSearchResultActions(static::class, $record),
    ];
}
```

Los enlaces siguen las mismas reglas que el menú: solo las páginas de relación
que el usuario puede abrir.
<!-- #endregion global-search -->

## Presets del menú

<!-- #region menu-presets -->
Publica la config:

```bash
php artisan vendor:publish --tag=filament-relation-navigator-config
```

```php
'menu' => [
    'preset' => 'github',   // 'simple', un preset tuyo o el nombre de una vista
    'presets' => [
        'simple' => 'filament-relation-navigator::menu.simple',
        'github' => 'filament-relation-navigator::menu.github',
    ],
    'searchable' => true,   // un campo de búsqueda encima de la lista, con cualquier preset
    'search_from' => 0,     // ...a partir de este número de elementos
    'badges' => 'lazy',     // los badges de los relation managers: 'lazy' al abrir el menú, true con la página, false desactivados
    'max_height' => '24rem',
    'separator' => true,    // páginas del registro y páginas de relación en dos bloques
    'mobile' => true,       // el menú encima del título en pantallas pequeñas
    'shortcut' => 'alt+shift+r', // abre el menú; null lo desactiva
],
```

**github** (el predeterminado): el último elemento del breadcrumb sigue siendo
texto y una flecha a su lado abre un panel con un título, un campo de búsqueda
y una marca en la página actual, como el selector de repositorios de GitHub.
El campo recibe el foco al abrirse el menú y Enter abre el primer resultado.

**simple**: el propio último elemento del breadcrumb abre la lista. Para el menú
de la 1.2 y anteriores, usa `'preset' => 'simple'` y `'searchable' => false`.

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/menu-simple-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/menu-simple-light.png" alt="El preset simple en la página de vista" width="520">
</picture>

### Badges

El menú muestra los badges de los relation managers, como en sus pestañas. Por
defecto **se cargan al abrir el menú**: hasta entonces el menú está cerrado,
así que la página se renderiza sin sus consultas, también en cada petición de
Livewire que vuelve a dibujar el breadcrumb. Mientras tardan se ve un spinner.

```php
'badges' => 'lazy', // predeterminado; true: con la página; false: sin badges
```

Desactívalos donde lo necesites:

```php
// Una página, o un recurso (static): sin badges en este menú
public function hasRelationNavigatorBadges(): bool
{
    return false;
}

// Un relation manager (con InteractsWithRelationPage): su badge se queda en
// la pestaña, no en el menú
protected static bool $hasRelationNavigatorBadge = false;
```

Un relation manager cuyo badge de pestaña es lazy en Filament
(`protected static ?bool $isBadgeLazyLoaded = true;`, o `$isBadgeDeferred` en
Filament 4.13) también es lazy en el menú, incluso con `'badges' => true`.

Las vistas del menú que no pueden cargar los badges después (vistas del paquete
publicadas antes de la 1.3, o presets propios que muestran `$item->badge` por
su cuenta) los reciben con la página, como antes: no desaparece nada.

**Teclado**: desde el campo de búsqueda, la flecha abajo entra en la lista; las
flechas arriba y abajo se mueven entre las páginas, Enter abre una, Esc cierra
el menú.

### Atajo y tooltip

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/tooltip-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/tooltip-light.png" alt="El tooltip del botón del menú con su atajo de teclado" width="470">
</picture>

El botón que abre el menú tiene un tooltip con el atajo, mostrado con las
teclas de Mac o de Windows/Linux. El atajo abre el menú desde cualquier parte
de la página, con el campo de búsqueda listo, como el selector de repositorios
de GitHub: <kbd>⌥</kbd> <kbd>⇧</kbd> <kbd>R</kbd> en un Mac, <kbd>Alt</kbd> <kbd>Shift</kbd> <kbd>R</kbd>
en los demás. Cámbialo en la config o por página:

```php
'shortcut' => 'mod+shift+p', // mod es ⌘ en un Mac y Ctrl en los demás
```

```php
public function getRelationNavigatorShortcut(): ?string
{
    return null; // sin atajo en esta página
}
```

Está pensado para no estorbar:

- reconoce la tecla física, así que funciona con cualquier distribución de
  teclado y con Option en un Mac (donde <kbd>⌥</kbd><kbd>⇧</kbd><kbd>R</kbd> escribe ‰);
- no hace nada mientras escribes en un campo;
- nunca toma un atajo de la búsqueda global del panel
  (`globalSearchKeyBindings()`): en ese caso el menú se queda sin atajo;
- solo se abre el menú visible (breadcrumb o pantallas pequeñas).

Teclas admitidas: `alt`, `shift`, `ctrl`, `meta`, `mod` más una letra, un
dígito o `/ . , ;`.

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/menu-github-search-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/menu-github-search-light.png" alt="El preset github con el campo de búsqueda" width="700">
</picture>

### Página, recurso o config

Cada opción tiene tres niveles. Gana la página, después el recurso, después la
config:

| Método de la página (static en el recurso) | Clave de config |
|---|---|
| `getRelationNavigatorPreset(): string` | `menu.preset` |
| `isRelationNavigatorSearchable(): bool` | `menu.searchable` |
| `hasRelationNavigatorBadges(): bool` | `menu.badges` |
| `hasDeferredRelationNavigatorBadges(): bool` | `menu.badges` = `'lazy'` |
| `getRelationNavigatorShortcut(): ?string` | `menu.shortcut` |
| `hasRelationNavigatorMobileMenu(): bool` | `menu.mobile` |
| `hasRelationNavigatorRecordNavigation(): bool` | `record_navigation` |
| `shouldRelationNavigatorRecordNavigationFollowList(): bool` | `record_navigation_follows_list` |
| `shouldRedirectRelationNavigatorTabs(): bool` | `redirect_relation_tabs` |

```php
// En una página
public function isRelationNavigatorSearchable(): bool
{
    return false;
}
```

```php
// En un recurso: el mismo método, static, para todas sus páginas
public static function hasRelationNavigatorRecordNavigation(): bool
{
    return true;
}
```

### Pantallas pequeñas

Filament oculta el breadcrumb por debajo de 40rem. Ahí el menú aparece encima
del título de la página, en cada página que usa `HasRelationNavigator`, con las
mismas opciones del preset. Desactívalo con `'mobile' => false`, o por página:

```php
public function hasRelationNavigatorMobileMenu(): bool
{
    return false;
}
```

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/mobile-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/mobile-light.png" alt="El menú en un teléfono, encima del título de la página" width="320">
</picture>

### Traducciones

32 idiomas, con los mismos códigos que Filament: alemán, árabe, búlgaro,
catalán, checo, chino (simplificado y tradicional), coreano, danés, eslovaco,
español, finlandés, francés, griego, hebreo, húngaro, indonesio, inglés,
italiano, japonés, neerlandés, noruego, persa, polaco, portugués (Portugal y
Brasil), rumano, ruso, sueco, turco, ucraniano y vietnamita. En los idiomas de
derecha a izquierda, las flechas de anterior y siguiente se invierten. Publica
las traducciones para cambiarlas o añadir un idioma:

```bash
php artisan vendor:publish --tag=filament-relation-navigator-translations
```

### Un preset propio

Parte de uno de los presets del paquete:

```bash
php artisan make:filament-relation-navigator-preset compact --from=github
```

El comando copia el preset en
`resources/views/filament/relation-navigator/compact.blade.php`. Regístralo y
cámbialo como quieras:

```php
'preset' => 'compact',
'presets' => [
    // ...
    'compact' => 'filament.relation-navigator.compact',
],
```

Cualquier nombre de vista también sirve como `preset`. Para cambiar los propios
presets del paquete, publica sus vistas:

```bash
php artisan vendor:publish --tag=filament-relation-navigator-views
```

Una vista de preset recibe:

| Variable             | Contenido                                                               |
| -------------------- | ----------------------------------------------------------------------- |
| `$label`             | El último elemento del breadcrumb                                       |
| `$items`             | Los elementos del menú: `key`, `label`, `url`, `icon`, `badge`, `badgeColor`, `isActive` |
| `$heading`           | "Switch page", traducido                                                |
| `$isSearchable`      | Si se muestra el campo de búsqueda                                      |
| `$hasSeparator`      | Si se separan las páginas del registro y las de relación en dos bloques |
| `$shortcut`          | El atajo, o null                                                        |
| `$shortcutKeys`      | Las teclas del atajo que se muestran: `['mac' => [...], 'other' => [...]]` |
| `$searchPlaceholder` | "Find a page…", traducido                                               |
| `$emptyLabel`        | "No pages found.", traducido                                            |
| `$maxHeight`         | `menu.max_height`                                                       |

Para darle a tu botón el tooltip y el atajo, incluye
`filament-relation-navigator::menu.partials.trigger-attributes` dentro de su
etiqueta. Cada elemento tiene también `isRelationPage()`. Para reutilizar el
campo de búsqueda, la lista (con el separador y la navegación con teclado) y la
línea de «sin resultados», incluye `filament-relation-navigator::menu.partials.list`
con `showCheck`.
<!-- #endregion menu-presets -->

## Personalizar las páginas de relación

<!-- #region customising-the-relation-pages -->
### Título, subtítulo y breadcrumb de una relación

Un relation manager puede definirlos para su propia página, a partir del
registro, sin clase de página:

```php
class OrdersRelationManager extends RelationManager
{
    public static function getRelationPageTitle(Model $ownerRecord): string|Htmlable|null
    {
        return "Pedidos de {$ownerRecord->name}";
    }

    public static function getRelationPageSubheading(Model $ownerRecord): string|Htmlable|null
    {
        return 'Últimos 12 meses';
    }

    public static function getRelationPageBreadcrumb(Model $ownerRecord): ?string
    {
        return 'Pedidos';
    }
}
```

Devuelve `null` para mantener el valor predeterminado. Una clase de página
escrita por ti (abajo) gana con sus propios `$title`, `$subheading` y
`$breadcrumb`. El menú y las pestañas conservan el título del relation manager.
Los grupos de relaciones no tienen relation manager, así que su página usa la
etiqueta del grupo.

### Una clase de página propia

Las páginas de relación generadas cubren casi todas las necesidades. Para
cambiar su título, su breadcrumb o cualquier otra cosa que pueda hacer una
página de Filament, escribe tú la clase de página; el paquete usará la tuya
para ese recurso:

```bash
php artisan make:filament-relation-navigator CustomerResource
```

```php
class CustomerRelationPage extends RelationPage
{
    protected static string $resource = CustomerResource::class;

    protected static ?array $relations = ['orders', 'invoices'];  // solo estas
    protected static array $excludedRelations = ['notes'];         // nunca estas
}
```

```php
public static function getPages(): array
{
    return [
        'index' => ListCustomers::route('/'),
        'view' => ViewCustomer::route('/{record}'),
        ...CustomerRelationPage::relationRoutes(),
    ];
}
```

La clase es una página normal de Filament: sobrescribe `getTitle()`,
`getBreadcrumbs()`, `getHeaderActions()`, usa tus propios traits, etc.
<!-- #endregion customising-the-relation-pages -->

## Subnavegación nativa del registro

<!-- #region native-record-sub-navigation -->
Los mismos elementos pueden alimentar la subnavegación del registro de
Filament:

```php
public static function getRecordSubNavigation(Page $page): array
{
    return RelationNavigator::getRecordSubNavigation($page);
}
```
<!-- #endregion native-record-sub-navigation -->

## Cómo funciona

<!-- #region how-it-works -->
- Las rutas se registran con el propio `Resource::registerRoutes()` de
  Filament, dentro del grupo de rutas de cada recurso: Filament se encarga del
  slug, el cluster, el recurso padre y el tenant.
- La clase de página de un recurso se escribe bajo demanda en
  `storage/framework/cache`, como hacen las real-time facades de Laravel, y se
  registra en Livewire. Funciona con `route:cache`.
- La página de relación incrusta el relation manager como lo hace Filament en
  las pestañas, así que su estado, acciones y modales se quedan en el relation
  manager.
<!-- #endregion how-it-works -->

## Agentes de IA

<!-- #region ai-agents -->
El paquete enseña a los agentes de IA para programar a usarlo, así que Claude
Code, Codex, Cursor, Copilot y similares escriben los traits y las opciones
correctamente:

- **Laravel Boost**: el paquete incluye guidelines y una skill
  (`relation-navigator-development`). Elige `wit3/filament-relation-navigator`
  cuando ejecutes `php artisan boost:install` (o `boost:update`).
- **llms.txt**: la documentación también se publica para los agentes, como
  índice ([llms.txt](https://wit3.github.io/filament-relation-navigator/llms.txt))
  y como un único archivo
  ([llms-full.txt](https://wit3.github.io/filament-relation-navigator/llms-full.txt)),
  en inglés. Cada página tiene también una versión en Markdown: añade `.md` a
  su URL.
- **Sin Boost**: indica a tu agente
  `vendor/wit3/filament-relation-navigator/resources/boost/skills/relation-navigator-development/SKILL.md`,
  por ejemplo desde tu `AGENTS.md` o `CLAUDE.md`.
<!-- #endregion ai-agents -->

## Tests

<!-- #region testing -->
```bash
composer test        # la suite de tests
composer analyse     # PHPStan, nivel 8
composer format      # Pint
```

La suite se ejecuta en Filament 4 y 5, Laravel 12 y 13, PHP 8.2 a 8.5, y cubre
recursos anidados, clusters, tenancy y el modo SPA.

Los tests en el navegador (el menú con búsqueda) necesitan Playwright:

```bash
npm install && npx playwright install chromium
vendor/bin/testbench filament:assets
composer test:browser
```
<!-- #endregion testing -->

## Changelog

<!-- #region changelog -->
Consulta el [CHANGELOG](https://github.com/wit3/filament-relation-navigator/blob/main/CHANGELOG.md) (en inglés).
<!-- #endregion changelog -->

## Contribuir

<!-- #region contributing -->
Los issues y las pull requests son bienvenidos. Antes de abrir una pull request,
ejecuta `composer test`, `composer analyse` y `composer format`.
<!-- #endregion contributing -->

## Seguridad

<!-- #region security -->
Informa de los problemas de seguridad en privado a través de los
[GitHub security advisories](https://github.com/wit3/filament-relation-navigator/security/advisories/new),
no en issues públicos.
<!-- #endregion security -->

## Créditos

<!-- #region credits -->
- [Salvatore Scalzi](https://github.com/wit3)
- [Todos los colaboradores](https://github.com/wit3/filament-relation-navigator/contributors)
<!-- #endregion credits -->

## Licencia

<!-- #region license -->
Licencia MIT. Consulta [LICENSE](https://github.com/wit3/filament-relation-navigator/blob/main/LICENSE.md).
<!-- #endregion license -->
