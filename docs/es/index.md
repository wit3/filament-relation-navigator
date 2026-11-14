---
layout: home

hero:
  name: Relation Navigator
  text: Una página real para cada relation manager
  tagline: Cada relation manager tiene su propia página y su URL. Un menú con búsqueda en el breadcrumb salta de una a otra. Para Filament 4 y 5.
  image:
    light: https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-light.png
    dark: https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-dark.png
    alt: Los pedidos de un cliente en su propia página, con el menú del breadcrumb abierto
  actions:
    - theme: brand
      text: Empezar
      link: /es/guide/quick-start
    - theme: alt
      text: Ver en GitHub
      link: https://github.com/wit3/filament-relation-navigator

features:
  - title: Un trait, ninguna clase de página
    details: Añade HasRelationPages al recurso. Nada que generar, nada que añadir a getPages() o al panel provider.
  - title: El relation manager que ya tienes
    details: La misma tabla, filtros, acciones, badges y permisos, en su propia ruta que puedes enlazar, guardar en marcadores y abrir en una pestaña nueva.
  - title: Un menú en el breadcrumb
    details: Todas las páginas del registro, con iconos, badges, búsqueda, navegación con teclado y un atajo. Dos presets o una vista propia.
  - title: Cabecera, acciones y widgets
    details: Muestra la ficha del registro, widgets y acciones de cabecera encima de la relación, tomados del recurso.
  - title: Registro anterior y siguiente
    details: Recorre los registros sin salir de la misma página, en el orden de la lista de la que vienes.
  - title: Actívalo todo, donde quieras
    details: Cada función se activa o desactiva de forma global, por recurso y por página o relation manager.
---

<div class="home-section">

## De pestaña a página

Las pestañas debajo de la página de vista se convierten en páginas propias. El mismo relation manager, la misma tabla, una URL que puedes compartir:

| Antes | Después |
| --- | --- |
| `/customers/1?relation=orders` | `/customers/1/orders` |
| `/customers/1?relation=invoices` | `/customers/1/invoices` |

Basta con dos traits: consulta el [inicio rápido](/es/guide/quick-start).

<img class="demo" src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/demo.gif" alt="El menú abierto con Alt Mayús R, filtrado por Invoices, y luego el cliente siguiente en la misma página">

</div>

<style>
.home-section { max-width: 1152px; margin: 64px auto 0; padding: 0 24px; }
.home-section h2 { border-top: 0; margin-top: 0; padding-top: 0; }
.home-section .demo { width: 100%; margin-top: 24px; border-radius: 12px; border: 1px solid var(--vp-c-divider); }
</style>
