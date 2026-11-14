# Guide de mise à jour

Le package suit le [versionnage sémantique](https://semver.org) : les versions mineures et correctives ne cassent jamais votre code. Cette page liste ce que vous voudrez peut-être vérifier lors d'une mise à jour. Le [changelog](/fr/reference/changelog) contient toutes les modifications.

```bash
composer update wit3/filament-relation-navigator
php artisan filament:assets
```

## Vers la 1.7

Rien à changer. Quand la navigation entre enregistrements est activée, **les ressources imbriquées affichent aussi précédent et suivant**, au sein du même enregistrement parent, dans l'ordre du relation manager du parent quand la ressource imbriquée est sa related resource (`$relatedResource`). Pour les masquer sur une ressource imbriquée, renvoyez `false` depuis `hasRelationNavigatorRecordNavigation()` sur ses pages ou sur la ressource.

## Vers la 1.6

Rien à changer.

- À partir de la 1.6.1, la mécanique interne du package est marquée `@internal` (voir le changelog) : si vous appelez `RelationNavigator::optsIn()`, `registerRoutes()` ou des fonctions similaires, passez à ce que liste la [référence de l'API](/fr/reference/api).
- Filament 4.13 est la version minimale acceptée à partir de la 1.6.1.

- **Les badges du menu se chargent à l'ouverture du menu**, et non avec la page. Pour l'ancien comportement : `'badges' => true`. Les vues du menu publiées avant la 1.3, et vos propres presets, continuent de les recevoir avec la page.
- L'action « Ouvrir comme page » a de nouvelles options (`relation_manager_action`) et de nouveaux points d'extension : voir [Ouvrir comme page](/fr/guide/open-as-page).

## Vers la 1.5

Rien à changer. Deux comportements sont différents :

- **Précédent et suivant suivent la liste d'où vous venez** (tri, filtres, recherche, onglet) au lieu de l'ordre des clés, quand la navigation entre enregistrements est activée. Pour garder l'ordre des clés : `'record_navigation_follows_list' => false`.
- **« Ouvrir comme page »** ouvre la page propre d'un relation manager avant celle d'un groupe de relations auquel il appartient aussi.

Si vous avez publié les vues du package, republiez `menu/partials/list.blade.php` : les relation managers dont le badge d'onglet est lazy le chargent désormais aussi ainsi dans le menu.

## Vers la 1.4

Rien à changer.

- Le lien **« Ouvrir comme page »** est désormais une action dans l'en-tête de la table du relation manager au lieu d'un lien au-dessus. Si vous voulez le désactiver quelque part, voir [Ouvrir comme page](/fr/guide/open-as-page) : config, ressource ou relation manager.
- `RelationNavigator::renderRelationManagerLink()` est déprécié : le package ne l'utilise plus. Il fonctionne encore pour un render hook à vous, et sera supprimé dans la 2.0.
- Chaque option du menu peut désormais se régler aussi sur une ressource : voir [Page, ressource ou config](/fr/guide/menu-presets#page-ressource-ou-config).

## Vers la 1.3

::: danger Correctif de sécurité
Les versions 1.0.0 à 1.2.1 n'appliquaient pas la policy `view` de l'enregistrement sur les pages de relation ([GHSA-v3mh-w7qp-37gc](https://github.com/wit3/filament-relation-navigator/security/advisories/GHSA-v3mh-w7qp-37gc)). Si vous utilisez des policies sur vos enregistrements, mettez à jour.
:::

- **Le menu par défaut est désormais le preset `github` avec le champ de recherche.** Si vous avez publié la config, vous gardez ce que vous avez réglé. Pour garder le menu de la 1.2 :

  ```php
  // config/filament-relation-navigator.php
  'menu' => [
      'preset' => 'simple',
      'searchable' => false,
  ],
  ```

- Le menu ne liste plus la page de modification aux utilisateurs qui ne peuvent pas modifier l'enregistrement.
- Nouvelles options, toutes avec une valeur par défaut dans le code : une config publiée depuis la 1.2 continue de fonctionner. Ajoutez-les à votre config quand vous voulez les changer : `relation_manager_link`, `record_navigation`, `redirect_relation_tabs`, et `'lazy'` pour `menu.badges`.
- Si vous avez publié les vues du package et voulez `'badges' => 'lazy'`, republiez `menu/partials/list.blade.php`.

## Vers la 1.2

- `HasRelationNavigator` déclare désormais `getRecord()` et `hasRecord()` : une page sans enregistrement échoue dès que sa classe est chargée, au lieu de plus tard. Les pages d'affichage, de modification et celles qui utilisent `InteractsWithRecord` les ont déjà.
