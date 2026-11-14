# Configuration

Publiez le fichier de config pour changer les valeurs par défaut de toutes les ressources :

```bash
php artisan vendor:publish --tag=filament-relation-navigator-config
```

Une config publiée continue de fonctionner d'une version mineure à l'autre : chaque clé a une valeur par défaut dans le code, donc il ne faut ajouter les nouvelles clés que si vous voulez les changer.

## Options

La plupart des options peuvent aussi se régler **par ressource** (une méthode statique sur la ressource) ou **par page ou relation manager** :

- **Options du menu** (`menu.*` avec une méthode, navigation entre enregistrements, redirection des onglets) : la page l'emporte, puis la ressource, puis la config.
- **« Ouvrir comme page »** et **le badge d'un relation manager dans le menu** : affichés seulement si tous les niveaux l'autorisent ; n'importe quel niveau peut les désactiver.
- **Liens de la recherche globale** : la méthode de la ressource remplace la config pour cette ressource ; un relation manager peut seulement s'exclure.
- **La liste mémorisée pour précédent et suivant** : décidée seulement par la ressource ou la config, car la page de liste ne peut pas interroger une page d'enregistrement.
- `menu.presets`, `menu.search_from`, `menu.max_height` et `menu.separator` se règlent uniquement dans la config.

| Clé | Par défaut | Ce qu'elle fait | Autres niveaux |
|---|---|---|---|
| `menu.preset` | `'github'` | L'apparence du menu : `github`, `simple`, un preset à vous ou un nom de vue | `getRelationNavigatorPreset()` |
| `menu.presets` | `simple`, `github` | Noms et vues des presets | |
| `menu.searchable` | `true` | Un champ de recherche au-dessus de la liste | `isRelationNavigatorSearchable()` |
| `menu.search_from` | `0` | Affiche le champ de recherche à partir de ce nombre d'éléments | |
| `menu.badges` | `'lazy'` | `'lazy'` : les badges se chargent à l'ouverture du menu ; `true` : avec la page ; `false` : aucun | `hasRelationNavigatorBadges()`, `hasDeferredRelationNavigatorBadges()` ; relation manager : `hasRelationNavigatorBadge()` |
| `menu.max_height` | `'24rem'` | Hauteur de la liste avant le défilement (une longueur CSS) | |
| `menu.separator` | `true` | Pages de l'enregistrement et pages de relation en deux blocs | |
| `menu.mobile` | `true` | Le menu au-dessus du titre sur les petits écrans | `hasRelationNavigatorMobileMenu()` |
| `menu.shortcut` | `'alt+shift+r'` | Raccourci clavier qui ouvre le menu ; `null` : aucun | `getRelationNavigatorShortcut()` |
| `relation_manager_link` | `true` | L'action « Ouvrir comme page » sur les tables des relation managers | ressource `hasRelationPageLinks()`, relation manager `hasRelationPageLink()` |
| `relation_manager_action` | lien avec une flèche | Apparence de cette action : `style`, `label`, `icon`, `icon_position`, `color`, `size`, `new_tab` | `configureRelationPageActionUsing()`, `configureRelationPageAction()` |
| `global_search_actions` | `false` | Liens vers les pages de relation sous les résultats de la recherche globale : `true` ou le maximum par résultat | ressource `hasRelationPageGlobalSearchActions()`, relation manager `hasRelationPageGlobalSearchAction()` |
| `record_navigation` | `false` | Boutons enregistrement précédent et suivant | `hasRelationNavigatorRecordNavigation()` |
| `record_navigation_follows_list` | `true` | Précédent et suivant suivent la liste d'où vient l'utilisateur | `shouldRelationNavigatorRecordNavigationFollowList()` |
| `redirect_relation_tabs` | `false` | `?relation=x` sur la page d'affichage ouvre la page de relation | `shouldRedirectRelationNavigatorTabs()` |

Les méthodes de page sont des méthodes d'instance sur une page d'enregistrement qui utilise `HasRelationNavigator` ; sur une ressource, la même méthode est statique et s'applique à toutes ses pages. Voir [Page, ressource ou config](/fr/guide/menu-presets#page-ressource-ou-config).

## Le fichier complet

Tel qu'il est fourni avec la version actuelle (les commentaires sont en anglais) :

<<< ../../../config/filament-relation-navigator.php
