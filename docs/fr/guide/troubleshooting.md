# Dépannage

## Le menu ne s'affiche pas

- La page doit utiliser `HasRelationNavigator`. Les pages de relation l'ont toujours.
- Le menu a besoin d'au moins deux éléments : la page elle-même et une autre.
- Une page qui construit son propre fil d'Ariane doit renvoyer `$this->withRelationNavigator([...])`.
- En dessous de 40rem, Filament masque le fil d'Ariane : le menu passe au-dessus du titre (`menu.mobile`).

## Le menu s'affiche mal ou la petite flèche est mal placée

Republiez la feuille de style du plugin, après chaque mise à jour :

```bash
php artisan filament:assets
```

## Une page de relation renvoie une 404

- Le relation manager n'a pas de page : la ressource doit utiliser `HasRelationPages`, ou le relation manager `InteractsWithRelationPage`, et il ne doit pas s'exclure.
- Son slug est égal à une clé de page de la ressource (`edit`, `view`...) : ces slugs sont ignorés. Choisissez-en un autre avec `$relationPageSlug`.
- Avec des routes en cache, remettez-les en cache après un déploiement : `php artisan route:cache`.
- La policy refuse l'enregistrement « comme introuvable » (`Response::denyAsNotFound()`) : la 404 est voulue.

## Une page de relation renvoie une 403

Une page de relation exige le `canAccess()` de la page d'affichage, la policy `view` de l'enregistrement (`update` pour une page de modification), le `canViewForRecord()` du relation manager et, si vous l'avez défini, le `canAccessRelationPage()` de la ressource. Vérifiez-les dans cet ordre.

## Les badges ne s'affichent pas

- `menu.badges` vaut `false`, la page ou la ressource les désactive, ou le relation manager le fait (`hasRelationNavigatorBadge()`).
- Par défaut, ils se chargent à l'ouverture du menu, avec un indicateur de chargement. S'il ne s'arrête jamais, cherchez une requête Livewire en échec dans la console du navigateur.
- Les vues publiées avant la 1.3 ne savent pas charger les badges plus tard : elles les reçoivent avec la page.

## Le raccourci ne fait rien

- Il ne fait rien pendant que vous tapez dans un champ.
- Un raccourci de la recherche globale du panel (`globalSearchKeyBindings()`) n'est jamais pris : choisissez-en un autre avec `menu.shortcut`.
- Une extension du navigateur ou le système d'exploitation utilise peut-être les mêmes touches.

## « Ouvrir comme page » est absent

- `relation_manager_link` vaut `false`, ou la ressource ou le relation manager la désactive.
- Le relation manager n'a pas de page à lui, ou vous êtes déjà dessus.
- Une table avec une vue `->header()` personnalisée n'a pas de place pour les actions d'en-tête.

## Précédent et suivant sont absents

- Ils sont désactivés par défaut : `record_navigation`.
- Ils disparaissent quand il n'y a pas d'autre enregistrement (sur une ressource imbriquée, aucun autre enregistrement du même parent).
- Pour suivre l'ordre de la liste, la liste doit avoir été ouverte pendant cette session, avec la navigation entre enregistrements activée dans la config ou sur la ressource.

## « A directory that only this user can write »

Le stockage est en lecture seule, donc les classes de page générées vont dans le répertoire temporaire du système, et ce répertoire appartient à un autre utilisateur. Rendez `storage/framework/cache` accessible en écriture, supprimez le répertoire indiqué dans le message, ou choisissez-en un dans un service provider :

```php
GeneratedRelationPages::useCachePath(storage_path('app/relation-pages'));
```

## Toujours bloqué

Posez votre question dans les [GitHub Discussions](https://github.com/wit3/filament-relation-navigator/discussions), ou ouvrez une [issue](https://github.com/wit3/filament-relation-navigator/issues) avec votre version de Filament et quelques lignes qui reproduisent le problème.
