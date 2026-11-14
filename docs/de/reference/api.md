# API-Referenz

Alles hier ist öffentliche API und fällt unter die [semantische Versionierung](/de/reference/upgrade): innerhalb von 1.x ändert sie sich nicht inkompatibel. Was hier nicht aufgeführt ist, ist intern.

## Traits

| Trait | Wo du ihn nutzt | Was er tut |
|---|---|---|
| `Concerns\HasRelationPages` | eine Resource | Jeder Relation Manager bekommt seine eigene Seite |
| `Concerns\InteractsWithRelationPage` | ein Relation Manager | Dieser Relation Manager bekommt eine Seite; liest die optionalen statischen Properties unten |
| `Concerns\HasRelationNavigator` | eine Datensatzseite (Ansicht, Bearbeitung, eigene) | Das Breadcrumb-Menü, und die Seite steht darin |

Alle im Namespace `Wit3\FilamentRelationNavigator`.

## Auf einer Resource

Alle statisch, alle optional.

| Methode | Wofür |
|---|---|
| `hasRelationPage(string $slug): bool` | Eine Relation ausschließen |
| `canAccessRelationPage(Model $record, string $slug): bool` | Eine Relationsseite für einen Datensatz sperren (Seiten, Menü, globale Suche, Weiterleitung) |
| `relationPageHeader(Schema $schema): Schema` | Header über der Relation auf jeder Relationsseite |
| `getRelationPageHeaderActions(): array` | Header-Aktionen der Relationsseiten |
| `getRelationPageHeaderWidgets(): array`, `getRelationPageFooterWidgets(): array` | Widgets der Relationsseiten (sie bekommen `$record`) |
| `hasRelationPageLinks(): bool` | „Als Seite öffnen“ an ihren Relation Managern |
| `configureRelationPageAction(Action $action, RelationManager $manager): Action` | Diese Aktion für diese Resource ändern |
| `hasRelationPageGlobalSearchActions(): bool` | Links der globalen Suche für diese Resource |
| jede [Options-Methode](/de/reference/configuration#optionen), statisch | Die Option für alle Seiten der Resource |

## An einem Relation Manager

Alle statisch, alle optional. Properties brauchen `InteractsWithRelationPage`; Methoden funktionieren auch ohne.

| Property oder Methode | Wofür |
|---|---|
| `$hasRelationPage`, `hasRelationPage(): bool` | Eine Seite für diesen Relation Manager oder nicht |
| `$relationPageSlug`, `getRelationPageSlug(): ?string` | Der Slug seiner Seite |
| `$hasRelationPageLink`, `hasRelationPageLink(): bool` | „Als Seite öffnen“ an seiner Tabelle |
| `$hasRelationPageGlobalSearchAction`, `hasRelationPageGlobalSearchAction(): bool` | Sein Link unter den Ergebnissen der globalen Suche |
| `$hasRelationNavigatorBadge`, `hasRelationNavigatorBadge(): bool` | Sein Badge im Menü (der Tab behält seinen) |
| `getRelationPageTitle(Model $ownerRecord)` | Titel seiner Seite; `null` behält den Standard |
| `getRelationPageSubheading(Model $ownerRecord)` | Untertitel seiner Seite |
| `getRelationPageBreadcrumb(Model $ownerRecord): ?string` | Letzte Breadcrumb seiner Seite |
| `relationPageHeader()`, `getRelationPageHeaderActions()`, `getRelationPageHeaderWidgets()`, `getRelationPageFooterWidgets()` | Wie auf der Resource, nur für seine Seite |
| `configureRelationPageAction(Action $action, RelationManager $manager): Action` | Seine Aktion „Als Seite öffnen“ ändern |

Auch Filaments eigenes `isBadgeLazyLoaded()` (oder `$isBadgeLazyLoaded`) macht sein Badge im Menü lazy.

## Auf einer Datensatzseite (`HasRelationNavigator`)

Überschreibe sie auf der Seite:

| Methode | Wofür |
|---|---|
| die [Options-Methoden](/de/reference/configuration#optionen) | Die Optionen dieser Seite |
| `getRelationNavigatorRecordNavigationQuery(): ?Builder` | Die Datensätze, durch die vorheriger und nächster blättern |
| `withRelationNavigator(array $breadcrumbs): array` | Das Menü zu selbst gebauten Breadcrumbs hinzufügen |

## Eine eigene Relationsseitenklasse

Erweitere `Pages\RelationPage` (oder führe `php artisan make:filament-relation-navigator CustomerResource` aus) und füge `...CustomerRelationPage::relationRoutes()` in `getPages()` ein.

| Member | Wofür |
|---|---|
| `protected static ?array $relations` | Nur diese Slugs |
| `protected static array $excludedRelations` | Nie diese Slugs |
| `protected static ?string $contextPage` | Schlüssel der Seite, für die sie steht (Standard: die Ansichtsseite, sonst die Bearbeitungsseite) |
| `$title`, `$subheading`, `$breadcrumb` und alle Methoden einer Filament-Seite | Wie auf jeder Filament-Seite |

## Hilfsmethoden

Statische Methoden von `RelationNavigator`:

| Methode | Wofür |
|---|---|
| `getItems(string $resource, Model $record, ...)` | Die Menüeinträge eines Datensatzes |
| `getRecordSubNavigation(Page $page): array` | Dieselben Einträge als Sub-Navigation von Filament |
| `getRelationManagerPageUrl(RelationManager $manager): ?string` | Die Seite eines Relation Managers, für eigene Buttons |
| `getGlobalSearchResultActions(string $resource, Model $record): array` | Die Links der globalen Suche, zum Zusammenführen mit deinen eigenen |
| `configureRelationPageActionUsing(Closure $callback): void` | Alle Aktionen „Als Seite öffnen“ ändern |
| `makeRelationPageAction(RelationManager $manager): Action` | Diese Aktion bauen |
| `getRelationPageClass(string $resource): ?string` | Die Relationsseitenklasse einer Resource |
| `RELATION_PAGE_ACTION` | Name der Aktion „Als Seite öffnen“ |

`RelationNavigatorItem` (in `$items`): `key`, `label`, `url`, `icon`, `badge`, `badgeColor`, `isActive`, `isBadgeDeferred`, `isRelationPage()`.

## Routen, Views und Befehle

- Routennamen: `{Routenbasis der Resource}.relations.{slug}`, für `getUrl('relations.orders', [...])`.
- Views: `filament-relation-navigator::menu.github`, `menu.simple`, `menu.mobile`, `menu.partials.list`, `menu.partials.trigger-attributes`. Ihre Variablen stehen unter [Ein eigenes Preset](/de/guide/menu-presets#ein-eigenes-preset).
- Befehle: `make:filament-relation-navigator {resource}`, `make:filament-relation-navigator-preset {name} --from=github|simple`.
- Übersetzungen: `filament-relation-navigator::navigator.*`.
