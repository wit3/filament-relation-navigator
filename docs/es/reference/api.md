# Referencia de la API

Todo lo que aparece aquí es API pública, cubierta por el [versionado semántico](/es/reference/upgrade): no cambia de forma incompatible dentro de la 1.x. Lo que no está en esta lista es interno.

## Traits

| Trait | Dónde se usa | Qué hace |
|---|---|---|
| `Concerns\HasRelationPages` | un recurso | Cada relation manager tiene su propia página |
| `Concerns\InteractsWithRelationPage` | un relation manager | Este relation manager tiene una página; lee las propiedades static opcionales de abajo |
| `Concerns\HasRelationNavigator` | una página del registro (vista, edición, personalizada) | El menú del breadcrumb, y la página aparece en él |

Todos en el namespace `Wit3\FilamentRelationNavigator`.

## En un recurso

Todos static, todos opcionales.

| Método | Para qué sirve |
|---|---|
| `hasRelationPage(string $slug): bool` | Dejar fuera una relación |
| `canAccessRelationPage(Model $record, string $slug): bool` | Vetar una página de relación para un registro (páginas, menú, búsqueda global, redirección) |
| `relationPageHeader(Schema $schema): Schema` | Cabecera encima de la relación en todas las páginas de relación |
| `getRelationPageHeaderActions(): array` | Acciones de cabecera de las páginas de relación |
| `getRelationPageHeaderWidgets(): array`, `getRelationPageFooterWidgets(): array` | Widgets de las páginas de relación (reciben `$record`) |
| `hasRelationPageLinks(): bool` | «Abrir como página» en sus relation managers |
| `configureRelationPageAction(Action $action, RelationManager $manager): Action` | Cambiar esa acción para este recurso |
| `hasRelationPageGlobalSearchActions(): bool` | Enlaces de la búsqueda global para este recurso |
| cualquier [método de opción](/es/reference/configuration#opciones), static | La opción para todas las páginas del recurso |

## En un relation manager

Todos static, todos opcionales. Las propiedades necesitan `InteractsWithRelationPage`; los métodos funcionan sin él.

| Propiedad o método | Para qué sirve |
|---|---|
| `$hasRelationPage`, `hasRelationPage(): bool` | Una página para este relation manager o no |
| `$relationPageSlug`, `getRelationPageSlug(): ?string` | El slug de su página |
| `$hasRelationPageLink`, `hasRelationPageLink(): bool` | «Abrir como página» en su tabla |
| `$hasRelationPageGlobalSearchAction`, `hasRelationPageGlobalSearchAction(): bool` | Su enlace bajo los resultados de la búsqueda global |
| `$hasRelationNavigatorBadge`, `hasRelationNavigatorBadge(): bool` | Su badge en el menú (la pestaña conserva el suyo) |
| `getRelationPageTitle(Model $ownerRecord)` | Título de su página; `null` mantiene el predeterminado |
| `getRelationPageSubheading(Model $ownerRecord)` | Subtítulo de su página |
| `getRelationPageBreadcrumb(Model $ownerRecord): ?string` | Último elemento del breadcrumb de su página |
| `relationPageHeader()`, `getRelationPageHeaderActions()`, `getRelationPageHeaderWidgets()`, `getRelationPageFooterWidgets()` | Como en el recurso, solo para su página |
| `configureRelationPageAction(Action $action, RelationManager $manager): Action` | Cambiar su acción «Abrir como página» |

El propio `isBadgeLazyLoaded()` de Filament (o `$isBadgeLazyLoaded`) también hace lazy su badge en el menú.

## En una página del registro (`HasRelationNavigator`)

Sobrescribe cualquiera de ellos en la página:

| Método | Para qué sirve |
|---|---|
| los [métodos de opción](/es/reference/configuration#opciones) | Las opciones de esta página |
| `getRelationNavigatorRecordNavigationQuery(): ?Builder` | Los registros que recorren anterior y siguiente |
| `withRelationNavigator(array $breadcrumbs): array` | Añadir el menú a un breadcrumb que construyes tú |

## Una clase de página de relación propia

Extiende `Pages\RelationPage` (o ejecuta `php artisan make:filament-relation-navigator CustomerResource`) y añade `...CustomerRelationPage::relationRoutes()` a `getPages()`.

| Miembro | Para qué sirve |
|---|---|
| `protected static ?array $relations` | Solo estos slugs |
| `protected static array $excludedRelations` | Nunca estos slugs |
| `protected static ?string $contextPage` | Clave de la página que representa (por defecto: la página de vista, si no la de edición) |
| `$title`, `$subheading`, `$breadcrumb` y todos los métodos de las páginas de Filament | Como en cualquier página de Filament |

## Helpers

Métodos static de `RelationNavigator`:

| Método | Para qué sirve |
|---|---|
| `getItems(string $resource, Model $record, ...)` | Los elementos del menú de un registro |
| `getRecordSubNavigation(Page $page): array` | Los mismos elementos como subnavegación de Filament |
| `getRelationManagerPageUrl(RelationManager $manager): ?string` | La página de un relation manager, para botones propios |
| `getGlobalSearchResultActions(string $resource, Model $record): array` | Los enlaces de la búsqueda global, para combinarlos con los tuyos |
| `configureRelationPageActionUsing(Closure $callback): void` | Cambiar todas las acciones «Abrir como página» |
| `makeRelationPageAction(RelationManager $manager): Action` | Construir esa acción |
| `getRelationPageClass(string $resource): ?string` | La clase de página de relación de un recurso |
| `RELATION_PAGE_ACTION` | Nombre de la acción «Abrir como página» |

`RelationNavigatorItem` (en `$items`): `key`, `label`, `url`, `icon`, `badge`, `badgeColor`, `isActive`, `isBadgeDeferred`, `isRelationPage()`.

## Rutas, vistas y comandos

- Nombres de las rutas: `{base de las rutas del recurso}.relations.{slug}`, para `getUrl('relations.orders', [...])`.
- Vistas: `filament-relation-navigator::menu.github`, `menu.simple`, `menu.mobile`, `menu.partials.list`, `menu.partials.trigger-attributes`. Sus variables están en [Un preset propio](/es/guide/menu-presets#un-preset-propio).
- Comandos: `make:filament-relation-navigator {resource}`, `make:filament-relation-navigator-preset {nombre} --from=github|simple`.
- Traducciones: `filament-relation-navigator::navigator.*`.
