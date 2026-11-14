# Solución de problemas

## El menú no aparece

- La página debe usar `HasRelationNavigator`. Las páginas de relación siempre lo tienen.
- El menú necesita al menos dos elementos: la propia página y otra más.
- Una página que construye su propio breadcrumb debe devolver `$this->withRelationNavigator([...])`.
- Por debajo de 40rem Filament oculta el breadcrumb: el menú pasa encima del título (`menu.mobile`).

## El menú se ve mal o la flecha está fuera de sitio

Vuelve a publicar la hoja de estilos del plugin después de cada actualización:

```bash
php artisan filament:assets
```

## Una página de relación da 404

- El relation manager no tiene página: el recurso necesita `HasRelationPages`, o el relation manager `InteractsWithRelationPage`, y no debe estar excluido.
- Su slug coincide con una clave de página del recurso (`edit`, `view`...): esos slugs se omiten. Elige otro con `$relationPageSlug`.
- Con las rutas en caché, vuelve a generarlas después de cada despliegue: `php artisan route:cache`.
- La policy deniega el registro «como no encontrado» (`Response::denyAsNotFound()`): el 404 es intencionado.

## Una página de relación da 403

Una página de relación necesita el `canAccess()` de la página de vista, la policy `view` del registro (`update` para una página de edición), el `canViewForRecord()` del relation manager y, si lo definiste, el `canAccessRelationPage()` del recurso. Compruébalos en ese orden.

## Los badges no aparecen

- `menu.badges` es `false`, la página o el recurso los desactivan, o lo hace el relation manager (`hasRelationNavigatorBadge()`).
- Por defecto se cargan al abrir el menú, con un spinner. Si el spinner no se detiene, busca en la consola del navegador una petición de Livewire fallida.
- Las vistas publicadas antes de la 1.3 no pueden cargar los badges después: los reciben con la página.

## El atajo no hace nada

- No hace nada mientras escribes en un campo.
- Un atajo de la búsqueda global del panel (`globalSearchKeyBindings()`) nunca se toma: elige otro con `menu.shortcut`.
- Una extensión del navegador o el sistema operativo pueden usar las mismas teclas.

## Falta «Abrir como página»

- `relation_manager_link` es `false`, o el recurso o el relation manager la desactivan.
- El relation manager no tiene página propia, o ya estás en ella.
- Una tabla con una vista `->header()` personalizada no tiene sitio para las acciones de cabecera.

## Faltan anterior y siguiente

- Están desactivados por defecto: `record_navigation`.
- Se ocultan cuando no hay otro registro (en un recurso anidado, ningún otro registro del mismo padre).
- Para seguir el orden de la lista, la lista debe haberse abierto en esta sesión, con la navegación entre registros activa en la config o en el recurso.

## «A directory that only this user can write»

El storage es de solo lectura, así que las clases de página generadas van al directorio temporal del sistema, y ese directorio pertenece a otro usuario. Haz que `storage/framework/cache` tenga permisos de escritura, elimina el directorio indicado en el mensaje, o elige uno en un service provider:

```php
GeneratedRelationPages::useCachePath(storage_path('app/relation-pages'));
```

## Sigues atascado

Pregunta en las [GitHub Discussions](https://github.com/wit3/filament-relation-navigator/discussions), o abre un [issue](https://github.com/wit3/filament-relation-navigator/issues) con tu versión de Filament y unas pocas líneas que reproduzcan el problema.
