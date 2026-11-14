# Riferimento API

Tutto quello che c'è qui è API pubblica, coperta dal [versionamento semantico](/it/reference/upgrade): nella 1.x non cambia in modo incompatibile. Quello che non è elencato è interno.

## Trait

| Trait | Dove si usa | Cosa fa |
|---|---|---|
| `Concerns\HasRelationPages` | una resource | Ogni relation manager ha la sua pagina |
| `Concerns\InteractsWithRelationPage` | un relation manager | Questo relation manager ha una pagina; legge le proprietà statiche facoltative qui sotto |
| `Concerns\HasRelationNavigator` | una pagina del record (visualizzazione, modifica, personalizzata) | Il menu nella breadcrumb, e la pagina compare nel menu |

Tutti nel namespace `Wit3\FilamentRelationNavigator`.

## Su una resource

Tutti statici, tutti facoltativi.

| Metodo | A cosa serve |
|---|---|
| `hasRelationPage(string $slug): bool` | Escludere una relazione |
| `canAccessRelationPage(Model $record, string $slug): bool` | Vietare una pagina di relazione per un record (pagine, menu, ricerca globale, redirect) |
| `relationPageHeader(Schema $schema): Schema` | Testata sopra la relazione in tutte le pagine di relazione |
| `getRelationPageHeaderActions(): array` | Azioni di testata delle pagine di relazione |
| `getRelationPageHeaderWidgets(): array`, `getRelationPageFooterWidgets(): array` | Widget delle pagine di relazione (ricevono `$record`) |
| `hasRelationPageLinks(): bool` | «Apri come pagina» sui suoi relation manager |
| `configureRelationPageAction(Action $action, RelationManager $manager): Action` | Modificare quell'azione per questa resource |
| `hasRelationPageGlobalSearchActions(): bool` | Link della ricerca globale per questa resource |
| qualsiasi [metodo di opzione](/it/reference/configuration#opzioni), statico | L'opzione per tutte le pagine della resource |

## Su un relation manager

Tutti statici, tutti facoltativi. Le proprietà richiedono `InteractsWithRelationPage`; i metodi funzionano anche senza.

| Proprietà o metodo | A cosa serve |
|---|---|
| `$hasRelationPage`, `hasRelationPage(): bool` | Una pagina per questo relation manager o no |
| `$relationPageSlug`, `getRelationPageSlug(): ?string` | Lo slug della sua pagina |
| `$hasRelationPageLink`, `hasRelationPageLink(): bool` | «Apri come pagina» sulla sua tabella |
| `$hasRelationPageGlobalSearchAction`, `hasRelationPageGlobalSearchAction(): bool` | Il suo link sotto i risultati della ricerca globale |
| `$hasRelationNavigatorBadge`, `hasRelationNavigatorBadge(): bool` | Il suo badge nel menu (la scheda tiene il suo) |
| `getRelationPageTitle(Model $ownerRecord)` | Titolo della sua pagina; `null` tiene il predefinito |
| `getRelationPageSubheading(Model $ownerRecord)` | Sottotitolo della sua pagina |
| `getRelationPageBreadcrumb(Model $ownerRecord): ?string` | Ultima voce della breadcrumb della sua pagina |
| `relationPageHeader()`, `getRelationPageHeaderActions()`, `getRelationPageHeaderWidgets()`, `getRelationPageFooterWidgets()` | Come sulla resource, solo per la sua pagina |
| `configureRelationPageAction(Action $action, RelationManager $manager): Action` | Modificare la sua azione «Apri come pagina» |

Anche `isBadgeLazyLoaded()` di Filament (o `$isBadgeLazyLoaded`) rende lazy il suo badge nel menu.

## Su una pagina del record (`HasRelationNavigator`)

Sovrascrivili sulla pagina:

| Metodo | A cosa serve |
|---|---|
| i [metodi di opzione](/it/reference/configuration#opzioni) | Le opzioni di questa pagina |
| `getRelationNavigatorRecordNavigationQuery(): ?Builder` | I record che precedente e successivo scorrono |
| `withRelationNavigator(array $breadcrumbs): array` | Aggiungere il menu a breadcrumb costruite da te |

## Una classe pagina di relazione tutta tua

Estendi `Pages\RelationPage` (o lancia `php artisan make:filament-relation-navigator CustomerResource`) e aggiungi `...CustomerRelationPage::relationRoutes()` a `getPages()`.

| Membro | A cosa serve |
|---|---|
| `protected static ?array $relations` | Solo questi slug |
| `protected static array $excludedRelations` | Mai questi slug |
| `protected static ?string $contextPage` | Chiave della pagina che rappresenta (predefinito: la pagina di visualizzazione, se no quella di modifica) |
| `$title`, `$subheading`, `$breadcrumb` e tutti i metodi delle pagine di Filament | Come in qualsiasi pagina di Filament |

## Funzioni di supporto

Metodi statici di `RelationNavigator`:

| Metodo | A cosa serve |
|---|---|
| `getItems(string $resource, Model $record, ...)` | Le voci del menu di un record |
| `getRecordSubNavigation(Page $page): array` | Le stesse voci come sotto-navigazione di Filament |
| `getRelationManagerPageUrl(RelationManager $manager): ?string` | La pagina di un relation manager, per pulsanti tuoi |
| `getGlobalSearchResultActions(string $resource, Model $record): array` | I link della ricerca globale, da unire ai tuoi |
| `configureRelationPageActionUsing(Closure $callback): void` | Modificare tutte le azioni «Apri come pagina» |
| `makeRelationPageAction(RelationManager $manager): Action` | Costruire quell'azione |
| `getRelationPageClass(string $resource): ?string` | La classe pagina di relazione di una resource |
| `RELATION_PAGE_ACTION` | Nome dell'azione «Apri come pagina» |

`RelationNavigatorItem` (in `$items`): `key`, `label`, `url`, `icon`, `badge`, `badgeColor`, `isActive`, `isBadgeDeferred`, `isRelationPage()`.

## Rotte, viste e comandi

- Nomi delle rotte: `{base delle rotte della resource}.relations.{slug}`, per `getUrl('relations.orders', [...])`.
- Viste: `filament-relation-navigator::menu.github`, `menu.simple`, `menu.mobile`, `menu.partials.list`, `menu.partials.trigger-attributes`. Le loro variabili sono in [Un preset tutto tuo](/it/guide/menu-presets#un-preset-tutto-tuo).
- Comandi: `make:filament-relation-navigator {resource}`, `make:filament-relation-navigator-preset {nome} --from=github|simple`.
- Traduzioni: `filament-relation-navigator::navigator.*`.
