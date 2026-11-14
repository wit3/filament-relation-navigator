# Configuración

Publica el archivo de config para cambiar los valores predeterminados de todos los recursos:

```bash
php artisan vendor:publish --tag=filament-relation-navigator-config
```

Una config publicada sigue funcionando entre versiones minor: cada clave tiene un valor predeterminado en el código, así que solo tienes que añadir las claves nuevas cuando quieras cambiarlas.

## Opciones

La mayoría de las opciones también se pueden definir **por recurso** (un método static en el recurso) o **por página o relation manager**:

- **Opciones del menú** (`menu.*` con un método, navegación entre registros, redirección de pestañas): gana la página, después el recurso, después la config.
- **«Abrir como página»** y **el badge de un relation manager en el menú**: se muestran solo cuando todos los niveles lo permiten; cualquier nivel puede desactivarlos.
- **Enlaces de la búsqueda global**: el método del recurso reemplaza la config para ese recurso; un relation manager solo puede excluirse.
- **La lista que se recuerda para anterior y siguiente**: la deciden solo el recurso o la config, porque la página de la lista no puede preguntarle a una página del registro.
- `menu.presets`, `menu.search_from`, `menu.max_height` y `menu.separator` solo están en la config.

| Clave | Predeterminado | Qué hace | Otros niveles |
|---|---|---|---|
| `menu.preset` | `'github'` | El aspecto del menú: `github`, `simple`, un preset tuyo o el nombre de una vista | `getRelationNavigatorPreset()` |
| `menu.presets` | `simple`, `github` | Nombres y vistas de los presets | |
| `menu.searchable` | `true` | Un campo de búsqueda encima de la lista | `isRelationNavigatorSearchable()` |
| `menu.search_from` | `0` | Muestra el campo de búsqueda a partir de este número de elementos | |
| `menu.badges` | `'lazy'` | `'lazy'`: los badges se cargan al abrir el menú; `true`: con la página; `false`: ninguno | `hasRelationNavigatorBadges()`, `hasDeferredRelationNavigatorBadges()`; relation manager: `hasRelationNavigatorBadge()` |
| `menu.max_height` | `'24rem'` | Altura de la lista antes de que aparezca el scroll (una longitud CSS) | |
| `menu.separator` | `true` | Páginas del registro y páginas de relación en dos bloques | |
| `menu.mobile` | `true` | El menú encima del título en pantallas pequeñas | `hasRelationNavigatorMobileMenu()` |
| `menu.shortcut` | `'alt+shift+r'` | Atajo de teclado que abre el menú; `null`: ninguno | `getRelationNavigatorShortcut()` |
| `relation_manager_link` | `true` | La acción «Abrir como página» en las tablas de los relation managers | recurso `hasRelationPageLinks()`, relation manager `hasRelationPageLink()` |
| `relation_manager_action` | enlace con flecha | Aspecto de esa acción: `style`, `label`, `icon`, `icon_position`, `color`, `size`, `new_tab` | `configureRelationPageActionUsing()`, `configureRelationPageAction()` |
| `global_search_actions` | `false` | Enlaces a las páginas de relación bajo los resultados de la búsqueda global: `true` o el máximo por resultado | recurso `hasRelationPageGlobalSearchActions()`, relation manager `hasRelationPageGlobalSearchAction()` |
| `record_navigation` | `false` | Botones de registro anterior y siguiente | `hasRelationNavigatorRecordNavigation()` |
| `record_navigation_follows_list` | `true` | Anterior y siguiente siguen la lista de la que viene el usuario | `shouldRelationNavigatorRecordNavigationFollowList()` |
| `redirect_relation_tabs` | `false` | `?relation=x` en la página de vista abre la página de relación | `shouldRedirectRelationNavigatorTabs()` |

Los métodos de página son métodos de instancia en una página del registro que usa `HasRelationNavigator`; en un recurso, el mismo método es static y se aplica a todas sus páginas. Consulta [Página, recurso o config](/es/guide/menu-presets#pagina-recurso-o-config).

## El archivo completo

Tal como viene con la versión actual (los comentarios están en inglés):

<<< ../../../config/filament-relation-navigator.php
