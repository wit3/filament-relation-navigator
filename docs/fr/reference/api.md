# Référence de l'API

Tout ce qui suit est de l'API publique, couverte par le [versionnage sémantique](/fr/reference/upgrade) : elle ne change pas de façon incompatible au sein de la 1.x. Tout ce qui n'est pas listé ici est interne.

## Traits

| Trait | Où l'utiliser | Ce qu'il fait |
|---|---|---|
| `Concerns\HasRelationPages` | une ressource | Chaque relation manager a sa propre page |
| `Concerns\InteractsWithRelationPage` | un relation manager | Ce relation manager a une page ; lit les propriétés statiques facultatives ci-dessous |
| `Concerns\HasRelationNavigator` | une page d'enregistrement (affichage, modification, personnalisée) | Le menu du fil d'Ariane, et la page y figure |

Tous dans le namespace `Wit3\FilamentRelationNavigator`.

## Sur une ressource

Toutes statiques, toutes facultatives.

| Méthode | Rôle |
|---|---|
| `hasRelationPage(string $slug): bool` | Exclure une relation |
| `canAccessRelationPage(Model $record, string $slug): bool` | Interdire une page de relation pour un enregistrement (pages, menu, recherche globale, redirection) |
| `relationPageHeader(Schema $schema): Schema` | En-tête au-dessus de la relation sur toutes les pages de relation |
| `getRelationPageHeaderActions(): array` | Actions d'en-tête des pages de relation |
| `getRelationPageHeaderWidgets(): array`, `getRelationPageFooterWidgets(): array` | Widgets des pages de relation (ils reçoivent `$record`) |
| `hasRelationPageLinks(): bool` | « Ouvrir comme page » sur ses relation managers |
| `configureRelationPageAction(Action $action, RelationManager $manager): Action` | Modifier cette action pour cette ressource |
| `hasRelationPageGlobalSearchActions(): bool` | Liens de recherche globale pour cette ressource |
| toute [méthode d'option](/fr/reference/configuration#options), statique | L'option pour toutes les pages de la ressource |

## Sur un relation manager

Toutes statiques, toutes facultatives. Les propriétés nécessitent `InteractsWithRelationPage` ; les méthodes fonctionnent sans.

| Propriété ou méthode | Rôle |
|---|---|
| `$hasRelationPage`, `hasRelationPage(): bool` | Une page pour ce relation manager ou non |
| `$relationPageSlug`, `getRelationPageSlug(): ?string` | Le slug de sa page |
| `$hasRelationPageLink`, `hasRelationPageLink(): bool` | « Ouvrir comme page » sur sa table |
| `$hasRelationPageGlobalSearchAction`, `hasRelationPageGlobalSearchAction(): bool` | Son lien sous les résultats de la recherche globale |
| `$hasRelationNavigatorBadge`, `hasRelationNavigatorBadge(): bool` | Son badge dans le menu (l'onglet garde le sien) |
| `getRelationPageTitle(Model $ownerRecord)` | Titre de sa page ; `null` garde la valeur par défaut |
| `getRelationPageSubheading(Model $ownerRecord)` | Sous-titre de sa page |
| `getRelationPageBreadcrumb(Model $ownerRecord): ?string` | Dernier élément du fil d'Ariane de sa page |
| `relationPageHeader()`, `getRelationPageHeaderActions()`, `getRelationPageHeaderWidgets()`, `getRelationPageFooterWidgets()` | Comme sur la ressource, pour sa page seulement |
| `configureRelationPageAction(Action $action, RelationManager $manager): Action` | Modifier son action « Ouvrir comme page » |

Le `isBadgeLazyLoaded()` de Filament (ou `$isBadgeLazyLoaded`) rend aussi son badge lazy dans le menu.

## Sur une page d'enregistrement (`HasRelationNavigator`)

Surchargez n'importe laquelle sur la page :

| Méthode | Rôle |
|---|---|
| les [méthodes d'option](/fr/reference/configuration#options) | Les options de cette page |
| `getRelationNavigatorRecordNavigationQuery(): ?Builder` | Les enregistrements que parcourent précédent et suivant |
| `withRelationNavigator(array $breadcrumbs): array` | Ajouter le menu à un fil d'Ariane que vous construisez vous-même |

## Votre propre classe de page de relation

Étendez `Pages\RelationPage` (ou lancez `php artisan make:filament-relation-navigator CustomerResource`), et ajoutez `...CustomerRelationPage::relationRoutes()` dans `getPages()`.

| Membre | Rôle |
|---|---|
| `protected static ?array $relations` | Seulement ces slugs |
| `protected static array $excludedRelations` | Jamais ces slugs |
| `protected static ?string $contextPage` | Clé de la page dont elle tient lieu (par défaut : la page d'affichage, sinon la page de modification) |
| `$title`, `$subheading`, `$breadcrumb`, et toutes les méthodes des pages Filament | Comme sur n'importe quelle page Filament |

## Fonctions utilitaires

Méthodes statiques de `RelationNavigator` :

| Méthode | Rôle |
|---|---|
| `getItems(string $resource, Model $record, ...)` | Les éléments du menu d'un enregistrement |
| `getRecordSubNavigation(Page $page): array` | Les mêmes éléments en sous-navigation Filament |
| `getRelationManagerPageUrl(RelationManager $manager): ?string` | La page d'un relation manager, pour vos propres boutons |
| `getGlobalSearchResultActions(string $resource, Model $record): array` | Les liens de recherche globale, à fusionner avec les vôtres |
| `configureRelationPageActionUsing(Closure $callback): void` | Modifier toutes les actions « Ouvrir comme page » |
| `makeRelationPageAction(RelationManager $manager): Action` | Construire cette action |
| `getRelationPageClass(string $resource): ?string` | La classe de page de relation d'une ressource |
| `RELATION_PAGE_ACTION` | Nom de l'action « Ouvrir comme page » |

`RelationNavigatorItem` (dans `$items`) : `key`, `label`, `url`, `icon`, `badge`, `badgeColor`, `isActive`, `isBadgeDeferred`, `isRelationPage()`.

## Routes, vues et commandes

- Noms des routes : `{base des routes de la ressource}.relations.{slug}`, pour `getUrl('relations.orders', [...])`.
- Vues : `filament-relation-navigator::menu.github`, `menu.simple`, `menu.mobile`, `menu.partials.list`, `menu.partials.trigger-attributes`. Leurs variables sont listées dans [Votre propre preset](/fr/guide/menu-presets#votre-propre-preset).
- Commandes : `make:filament-relation-navigator {resource}`, `make:filament-relation-navigator-preset {nom} --from=github|simple`.
- Traductions : `filament-relation-navigator::navigator.*`.
