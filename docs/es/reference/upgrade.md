# Guía de actualización

El paquete sigue el [versionado semántico](https://semver.org/lang/es/): las versiones minor y patch nunca rompen tu código. Esta página recoge lo que conviene revisar al actualizar. El [changelog](/es/reference/changelog) tiene todos los cambios.

```bash
composer update wit3/filament-relation-navigator
php artisan filament:assets
```

## A la 1.7

Nada que cambiar. Cuando la navegación entre registros está activa, **los recursos anidados también muestran anterior y siguiente**, dentro del mismo registro padre, en el orden del relation manager del padre cuando el recurso anidado es su recurso relacionado (`$relatedResource`). Para ocultarlos en un recurso anidado, devuelve `false` desde `hasRelationNavigatorRecordNavigation()` en sus páginas o en el recurso.

## A la 1.6

Nada que cambiar.

- Desde la 1.6.1, el funcionamiento interno del paquete está marcado como `@internal` (consulta el changelog): si llamas a `RelationNavigator::optsIn()`, `registerRoutes()` o helpers parecidos, pasa a lo que recoge la [referencia de la API](/es/reference/api).
- Desde la 1.6.1, la versión mínima de Filament es la 4.13.

- **Los badges del menú se cargan al abrir el menú**, no con la página. Para el comportamiento anterior: `'badges' => true`. Las vistas del menú publicadas antes de la 1.3, y los presets propios, siguen recibiéndolos con la página.
- La acción «Abrir como página» tiene nuevas opciones (`relation_manager_action`) y nuevos hooks: consulta [Abrir como página](/es/guide/open-as-page).

## A la 1.5

Nada que cambiar. Hay dos comportamientos distintos:

- **Anterior y siguiente siguen la lista de la que vienes** (orden, filtros, búsqueda, pestaña) en lugar del orden de las claves, cuando la navegación entre registros está activa. Para mantener el orden de las claves: `'record_navigation_follows_list' => false`.
- **«Abrir como página»** abre la página propia de un relation manager antes que la página de un grupo de relaciones al que también pertenece.

Si publicaste las vistas del paquete, vuelve a publicar `menu/partials/list.blade.php`: los relation managers con un badge de pestaña lazy ahora también lo cargan así en el menú.

## A la 1.4

Nada que cambiar.

- El enlace **«Abrir como página»** ahora es una acción en la cabecera de la tabla del relation manager en lugar de un enlace encima de ella. Si quieres desactivarlo en algún sitio, consulta [Abrir como página](/es/guide/open-as-page): config, recurso o relation manager.
- `RelationNavigator::renderRelationManagerLink()` está obsoleto: el paquete ya no lo usa. Sigue funcionando para un render hook propio y se eliminará en la 2.0.
- Ahora cada opción del menú también se puede definir en un recurso: consulta [Página, recurso o config](/es/guide/menu-presets#pagina-recurso-o-config).

## A la 1.3

::: danger Corrección de seguridad
Las versiones de la 1.0.0 a la 1.2.1 no aplicaban la policy `view` del registro en las páginas de relación ([GHSA-v3mh-w7qp-37gc](https://github.com/wit3/filament-relation-navigator/security/advisories/GHSA-v3mh-w7qp-37gc)). Si usas policies en tus registros, actualiza.
:::

- **El menú predeterminado ahora es el preset `github` con el campo de búsqueda.** Si publicaste la config, conservas lo que configuraste. Para mantener el menú de la 1.2:

  ```php
  // config/filament-relation-navigator.php
  'menu' => [
      'preset' => 'simple',
      'searchable' => false,
  ],
  ```

- El menú ya no muestra la página de edición a los usuarios que no pueden editar el registro.
- Nuevas opciones, todas con un valor predeterminado en el código: una config publicada desde la 1.2 sigue funcionando. Añádelas a tu config cuando quieras cambiarlas: `relation_manager_link`, `record_navigation`, `redirect_relation_tabs` y `'lazy'` para `menu.badges`.
- Si publicaste las vistas del paquete y quieres `'badges' => 'lazy'`, vuelve a publicar `menu/partials/list.blade.php`.

## A la 1.2

- `HasRelationNavigator` ahora declara `getRecord()` y `hasRecord()`: una página sin registro falla en cuanto se carga su clase, en lugar de más tarde. Las páginas de vista y de edición, y las que usan `InteractsWithRecord`, ya los tienen.
