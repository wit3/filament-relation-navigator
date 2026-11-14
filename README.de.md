<img class="filament-hidden" src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/cover.jpg" alt="Relation Navigator for Filament">

# Relation Navigator für Filament

<p class="filament-hidden">
    <a href="https://packagist.org/packages/wit3/filament-relation-navigator"><img src="https://img.shields.io/packagist/v/wit3/filament-relation-navigator.svg?style=flat-square" alt="Latest version on Packagist"></a>
    <a href="https://github.com/wit3/filament-relation-navigator/actions/workflows/tests.yml"><img src="https://img.shields.io/github/actions/workflow/status/wit3/filament-relation-navigator/tests.yml?branch=main&label=tests&style=flat-square" alt="Tests"></a>
    <a href="https://packagist.org/packages/wit3/filament-relation-navigator"><img src="https://img.shields.io/packagist/dt/wit3/filament-relation-navigator.svg?style=flat-square" alt="Total downloads"></a>
    <a href="LICENSE.md"><img src="https://img.shields.io/packagist/l/wit3/filament-relation-navigator.svg?style=flat-square" alt="License"></a>
</p>

> **Dokumentation:** Anleitungen, Konfigurationsreferenz, Upgrade-Anleitung und Suche auf
> **[wit3.github.io/filament-relation-navigator/de](https://wit3.github.io/filament-relation-navigator/de/)**.
>
> **Neueste Version:** [Was ist neu](https://wit3.github.io/filament-relation-navigator/de/reference/changelog) [![Latest version on Packagist](https://img.shields.io/packagist/v/wit3/filament-relation-navigator.svg?style=flat-square&label=version)](https://github.com/wit3/filament-relation-navigator/releases/latest)
>
> **In anderen Sprachen:** die Dokumentation auf
> [English](https://wit3.github.io/filament-relation-navigator/) (das Original) · [Italiano](https://wit3.github.io/filament-relation-navigator/it/) · [Español](https://wit3.github.io/filament-relation-navigator/es/) · [Português](https://wit3.github.io/filament-relation-navigator/pt/) · [Français](https://wit3.github.io/filament-relation-navigator/fr/) · [中文](https://wit3.github.io/filament-relation-navigator/zh/) · [日本語](https://wit3.github.io/filament-relation-navigator/ja/) · [Русский](https://wit3.github.io/filament-relation-navigator/ru/) · [العربية](https://wit3.github.io/filament-relation-navigator/ar/).
> Jede Sprache hat auch ihr README in diesem Repository (`README.md`, `README.it.md`...).

<!-- #region intro -->
Gib jedem Relation Manager einer Filament-Resource **eine eigene Seite** und
wechsle zwischen ihnen über ein **Menü in der Breadcrumb**.

- `/customers/1?relation=orders` wird zu **`/customers/1/orders`**: eine echte
  Route, die du verlinken, als Lesezeichen speichern und in einem neuen Tab
  öffnen kannst.
- Die Seite zeigt **den Relation Manager, den du schon hast**: dieselbe
  Tabelle, dieselben Filter, Aktionen, Badges und Berechtigungen. Nichts wird
  doppelt angelegt.
- Über der Relation kannst du einen **Header** einblenden (die Karte des
  Datensatzes, Widgets, Aktionen), übernommen aus der Resource.
- Die letzte Breadcrumb öffnet ein **Menü** mit den Seiten des Datensatzes und
  allen Relationen, die der Benutzer sehen darf, mit Icons und Badges. Zwei
  Varianten, optionale Suche und Tastaturnavigation, eine eigene View und eine
  Version für kleine Bildschirme.
- **Vorheriger und nächster Datensatz** auf derselben Seite, eine Aktion
  **„Als Seite öffnen“** an jeder Relation-Manager-Tabelle und Links auf alte
  Tabs, die auf den neuen Seiten landen: alles global, pro Resource oder pro
  Seite schaltbar.
- **Ein Trait, keine Seitenklassen**: keine Dateien zu generieren, nichts in
  `getPages()` oder im Panel Provider einzutragen.
- **32 Sprachen**, auch solche von rechts nach links, mit den Codes von
  Filament. Die Dokumentation gibt es in 10 Sprachen.

<img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/demo.gif" alt="Das Menü, geöffnet mit Alt Umschalt R, gefiltert auf Invoices, dann der nächste Kunde auf derselben Seite" width="880">
<!-- #endregion intro -->

## Voraussetzungen

<!-- #region requirements -->
| Paket    | Version              |
| -------- | -------------------- |
| PHP      | 8.2+                 |
| Laravel  | 12, 13               |
| Filament | 4.13+, 5.x           |
<!-- #endregion requirements -->

## Installation

<!-- #region installation -->
```bash
composer require wit3/filament-relation-navigator
php artisan filament:assets
```

Führe `filament:assets` nach jedem Update erneut aus: Filament kopiert das
Stylesheet des Plugins nach `public/`.
<!-- #endregion installation -->

## Schnellstart

<!-- #region quick-start -->
**1. Gib den Relationen eine Seite.** Füge den Trait zur Resource hinzu:

```php
use Wit3\FilamentRelationNavigator\Concerns\HasRelationPages;

class CustomerResource extends Resource
{
    use HasRelationPages;

    public static function getRelations(): array
    {
        return [
            'orders' => OrdersRelationManager::class,
            'invoices' => InvoicesRelationManager::class,
            NotesRelationManager::class,
        ];
    }
}
```

Jetzt hat jede Relation ihre Seite: `/customers/1/orders`,
`/customers/1/invoices`, `/customers/1/notes`.

**2. Füge das Menü zur Ansichtsseite hinzu:**

```php
use Wit3\FilamentRelationNavigator\Concerns\HasRelationNavigator;

class ViewCustomer extends ViewRecord
{
    use HasRelationNavigator;
}
```

Das ist alles. Die Relationsseiten haben das Menü bereits, und die Tabs auf
der Ansichtsseite funktionieren weiter wie bisher.

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-light.png" alt="Die Bestellungen eines Kunden auf ihrer eigenen Seite, mit geöffnetem Breadcrumb-Menü">
</picture>
<!-- #endregion quick-start -->

## Relationsseiten

<!-- #region relation-pages -->
### Aktivieren

Aktiviere sie auf der Resource, für alle Relation Manager, oder nur an den
Relation Managern, die du willst:

```php
use Wit3\FilamentRelationNavigator\Concerns\InteractsWithRelationPage;

class OrdersRelationManager extends RelationManager
{
    use InteractsWithRelationPage;
}
```

| Einrichtung                                 | Relationen mit einer Seite          |
| ------------------------------------------- | ----------------------------------- |
| `HasRelationPages` auf der Resource         | alle, außer den ausgeschlossenen    |
| `InteractsWithRelationPage` an einem Manager | nur die Manager, die ihn nutzen    |
| Eine eigene Seitenklasse (siehe unten)      | alle, außer den ausgeschlossenen    |

Um einige Relationen auszuschließen:

```php
// Auf der Resource
public static function hasRelationPage(string $slug): bool
{
    return $slug !== 'notes';
}

// An einem Relation Manager, mit InteractsWithRelationPage
protected static bool $hasRelationPage = false;
```

Ein Relation Manager, den zwei Resources teilen, ist für beide aktiv: schließe
ihn mit `hasRelationPage()` der Resource bei einer davon aus.

### URLs und Slugs

| Eintrag in `getRelations()`                     | URL                         |
| ----------------------------------------------- | --------------------------- |
| `'orders' => OrdersRelationManager::class`      | `/customers/1/orders`       |
| `SupportTicketsRelationManager::class` (ohne Schlüssel) | `/customers/1/support-tickets` |
| `RelationGroup::make('Activity', [...])`        | `/customers/1/activity`     |

Der eigene Slug eines Relation Managers hat Vorrang
(`protected static ?string $relationPageSlug = 'tickets';`), dann der Schlüssel
in `getRelations()`, dann der Name der Relation in kebab-case (oder das Label
der Gruppe: gib Gruppen einen Schlüssel, denn ein übersetztes Label ändert sich
mit der Sprache). Slugs, die einem Seitenschlüssel der Resource entsprechen
(`edit`, `view`...), werden übersprungen, ebenso eine zweite Relation mit einem
bereits vergebenen Slug.

Jede Seite ist eine benannte Route, `relations.{slug}`:

```php
CustomerResource::getUrl('relations.orders', ['record' => $customer]);
```

### Berechtigungen und Verhalten

Der Relation Manager bekommt die **Ansichtsseite** der Resource als
`$pageClass` (die Bearbeitungsseite, wenn es keine Ansichtsseite gibt). Daher
verhalten sich `isReadOnly()`, `canViewForRecord()`, `getBadge()` und alle
anderen Prüfungen genau wie im Tab. Die Ansichtsseite wird über ihren
Schlüssel gefunden (`view`, `edit` oder `$contextPage` in einer eigenen
Seitenklasse) oder über ihre Klasse.

Eine Relationsseite braucht, was die Ansichtsseite braucht (ihr `canAccess()`
und die `view`-Policy des Datensatzes; `update`, wenn sie für die
Bearbeitungsseite steht), und das `canViewForRecord()` des Managers. Ohne
Ansichts- oder Bearbeitungsseite gilt die `view`-Policy. Eine Policy, die mit
„nicht gefunden“ ablehnt, ergibt einen 404, ein unbekannter Slug ebenso. Das
Menü, die globale Suche und „Als Seite öffnen“ verlinken nur die Seiten, die
der Benutzer öffnen darf.

Relationsseiten führen den eigenen Code der Ansichtsseite nicht aus: Prüfungen
in ihrem `authorizeAccess()` oder ein pro Benutzer gefiltertes
`getRelationManagers()` gelten dort nicht. Leg sie in Policies oder in
`canViewForRecord()` ab, oder sperre eine Relationsseite pro Datensatz auf der
Resource:

```php
public static function canAccessRelationPage(Model $record, string $slug): bool
{
    return $slug !== 'audit-logs' || auth()->user()->isAdmin();
}
```
<!-- #endregion relation-pages -->

## Header, Aktionen und Widgets

<!-- #region header-actions-and-widgets -->
Über der Relation kann eine Relationsseite einen Teil der Ansicht des
Datensatzes, Header-Aktionen und Widgets zeigen. Definiere sie auf der Resource
für alle Relationsseiten oder an einem Relation Manager für seine eigene Seite:

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-light.png" alt="Eine Relationsseite mit Widgets, der Kundenkarte und einer Header-Aktion über den Bestellungen" width="880">
</picture>

```php
public static function relationPageHeader(Schema $schema): Schema
{
    // Die ganze Infolist (static::infolist($schema)) oder ein Teil davon.
    return $schema->components([
        CustomerCard::make(),
    ]);
}

public static function getRelationPageHeaderActions(): array
{
    return [
        Action::make('email')
            ->url(fn (Customer $record): string => "mailto:{$record->email}"),
    ];
}

public static function getRelationPageHeaderWidgets(): array
{
    return [CustomerStats::class]; // bekommt den Datensatz als $record
}

public static function getRelationPageFooterWidgets(): array
{
    return [];
}
```

- Der Header eines Relation Managers ersetzt den der Resource; Aktionen und
  Widgets von beiden werden angezeigt, die der Resource zuerst.
- Header-Aktionen wirken auf den Datensatz, zu dem die Relation gehört.
- Mit einem Header wird der Name des Datensatzes nicht unter dem Titel
  wiederholt.
- Was es nur in der Klasse der Ansichtsseite gibt (ihre eigenen Methoden und
  Modals), ist von hier aus nicht erreichbar: verschiebe es in die Resource, in
  eine Action-Klasse oder in einen Trait.
<!-- #endregion header-actions-and-widgets -->

## Das Breadcrumb-Menü

<!-- #region the-breadcrumb-menu -->
`HasRelationNavigator` braucht eine Seite mit Datensatz: `ViewRecord`,
`EditRecord` und Seiten mit `InteractsWithRecord` haben die beiden Methoden,
die er deklariert, `getRecord()` und `hasRecord()`, bereits. Auf einer Seite
ohne Datensatz meldet PHP das, sobald die Klasse geladen wird.

Eine Datensatzseite steht genau dann im Menü, wenn sie `HasRelationNavigator`
nutzt: die Ansichtsseite, die Bearbeitungsseite oder jede eigene Seite des
Datensatzes. Auf diesen Seiten öffnet die letzte Breadcrumb das Menü. Die
Relationsseiten stehen immer in der Liste, nach den Datensatzseiten, mit den
Icons und Badges der Relation Manager, und nur, wenn der Benutzer sie sehen
darf.

Wenn die Seite ihre Breadcrumbs schon selbst baut, übergib sie an
`withRelationNavigator()`:

```php
public function getBreadcrumbs(): array
{
    return $this->withRelationNavigator([
        CustomerResource::getUrl('index') => 'Customers',
        $this->getBreadcrumb(),
    ]);
}
```

Eine eigene Seite, die `InteractsWithRecord` selbst nutzt, statt `ViewRecord`
oder `EditRecord` zu erweitern, hat zwei `getBreadcrumbs()`-Methoden. Sofern
sie keine eigene definiert, sag PHP, welche gewinnt:

```php
class CustomerActivity extends Page
{
    use HasRelationNavigator, InteractsWithRecord {
        HasRelationNavigator::getBreadcrumbs insteadof InteractsWithRecord;
        InteractsWithRecord::getBreadcrumbs as getRecordBreadcrumbs;
    }
}
```

Das Menü nutzt `$navigationLabel` und `$navigationIcon` der Seite und zeigt
sie nur, wenn `canAccess()` es für den Datensatz erlaubt.
<!-- #endregion the-breadcrumb-menu -->

## Als Seite öffnen

<!-- #region open-as-page -->
Auf der Ansichts- und der Bearbeitungsseite bekommt jeder Relation Manager mit
eigener Seite eine Aktion **Als Seite öffnen** im Header seiner Tabelle, neben
dem Titel und vor seinen eigenen Aktionen. Sie öffnet die eigene Seite des
Relation Managers oder die Seite seiner Relationsgruppe, wenn er keine eigene
hat. Auf der Seite einer Gruppe bekommen die Manager mit eigener Seite die
Aktion ebenfalls; auf seiner eigenen Seite verlinkt ein Manager nie auf sich
selbst.

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/open-as-page-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/open-as-page-light.png" alt="Die Aktion Als Seite öffnen im Header der Tabelle Orders, auf der Ansichtsseite" width="880">
</picture>

Schalte sie ab, wo nötig:

```php
// config/filament-relation-navigator.php: überall
'relation_manager_link' => false,
```

```php
// Auf einer Resource: alle ihre Relation Manager
public static function hasRelationPageLinks(): bool
{
    return false;
}
```

```php
// An einem Relation Manager (mit InteractsWithRelationPage)
protected static bool $hasRelationPageLink = false;

// oder, ohne den Trait
public static function hasRelationPageLink(): bool
{
    return false;
}
```

### Ihr Aussehen

Standardmäßig ein Link mit Pfeil. Ändere es in der Config:

```php
'relation_manager_action' => [
    'style' => 'button',        // link, button oder icon_button (das Label wird zum Tooltip)
    'label' => 'Ganze Seite',   // Text oder Übersetzungsschlüssel; null: „Als Seite öffnen“, übersetzt
    'icon' => 'heroicon-m-arrow-top-right-on-square', // jedes Filament-Icon, oder null
    'icon_position' => 'after', // oder before
    'color' => 'gray',
    'size' => null,             // sm, md, lg...
    'new_tab' => false,
],
```

Oder ändere die Filament-Aktion selbst, für alles außer ihrer URL und wann sie
erscheint: das legt das Package fest. Jede Ebene bekommt die Aktion so, wie die
vorherige sie hinterlassen hat:

```php
// Ein Service Provider: alle Relation Manager
RelationNavigator::configureRelationPageActionUsing(
    fn (Action $action, RelationManager $relationManager) => $action->outlined()->badge('neu'),
);
```

```php
// Eine Resource oder ein Relation Manager: nur dort
public static function configureRelationPageAction(Action $action): Action
{
    return $action->iconButton()->tooltip('Die Bestellungen auf ihrer eigenen Seite öffnen');
}
```

`RelationNavigator::getRelationManagerPageUrl($relationManager)` liefert
dieselbe URL, für einen eigenen Button.
<!-- #endregion open-as-page -->

## Vorheriger und nächster Datensatz

<!-- #region previous-and-next-record -->
Zwei Buttons vor den Header-Aktionen öffnen den vorherigen oder den nächsten
Datensatz **auf derselben Seite**: von den Rechnungen eines Kunden zu den
Rechnungen des nächsten Kunden.

<img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/record-navigation.gif" alt="Vorheriger und nächster Datensatz: von den Rechnungen eines Kunden zu denen des nächsten, mit einem Tooltip, der den Datensatz nennt" width="880">

Ein Datensatz, der diese Seite nicht öffnen kann, öffnet sich auf seiner
ersten Seite im Menü. Standardmäßig sind die Buttons aus, weil sie pro Render
ein paar Queries hinzufügen:

```php
'record_navigation' => true,
```

Sie folgen **der Liste, von der der Benutzer kommt**: ihrer Sortierung, ihren
Filtern, ihrer Suche und ihrem Tab, wie auf dem Bildschirm (bis zu 100
Datensätze; etwa 30 mit UUID-Schlüsseln). Am ersten oder letzten Datensatz
dieser Liste geht es nicht weiter, statt in eine andere Reihenfolge zu
springen. Ein Datensatz, der nicht in der Liste ist (über einen Link geöffnet),
folgt der Reihenfolge der Schlüssel. Um immer der Reihenfolge der Schlüssel zu
folgen:

```php
'record_navigation_follows_list' => false,
```

Die Liste merkt sich ihre Datensätze nur, wenn die Datensatznavigation in der
Config oder auf der Resource aktiv ist (`hasRelationNavigatorRecordNavigation()`),
da die Listenseite keine Datensatzseite fragen kann.

Sie laufen über die eigene Query der Resource (`getRecordRouteBindingEloquentQuery()`),
also gelten Tenancy und deine Scopes, und überspringen die Datensätze, die der
Benutzer nicht sehen darf. Pro Seite:

```php
public function hasRelationNavigatorRecordNavigation(): bool
{
    return true;
}

public function getRelationNavigatorRecordNavigationQuery(): ?Builder
{
    return Customer::query()->where('active', true);
}
```

Bei einer **verschachtelten Resource** bleiben sie innerhalb des übergeordneten
Datensatzes: von einer Bestellung eines Kunden zur nächsten Bestellung
desselben Kunden. Die Liste ist dort der Relation Manager der übergeordneten
Resource: sie folgen seiner Reihenfolge, wenn die verschachtelte Resource seine
Related Resource ist (`protected static ?string $relatedResource`), außer mit
dem Session-Treiber `cookie`, der nur die Liste der übergeordneten Resource
behält.
<!-- #endregion previous-and-next-record -->

## Links auf Relation-Manager-Tabs

<!-- #region links-to-relation-manager-tabs -->
Sobald die Relationsseiten die Tabs ersetzen, können alte Links wie
`/customers/1?relation=orders` stattdessen `/customers/1/orders` öffnen:

```php
'redirect_relation_tabs' => true,
```

Tabs ohne Relationsseite oder solche, die der Datensatz nicht sehen kann,
bleiben, wo sie sind.
<!-- #endregion links-to-relation-manager-tabs -->

## Globale Suche

<!-- #region global-search -->
Ergebnisse der globalen Suche können direkt auf die Relationsseiten des
Datensatzes verlinken: einen Kunden suchen, zu seinen Bestellungen springen.
Standardmäßig aus, da dafür für jedes Ergebnis `canViewForRecord()` der
Relation Manager läuft:

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/global-search-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/global-search-light.png" alt="Ein Kunde in den Ergebnissen der globalen Suche, mit Links zu seinen Bestellungen, Rechnungen, Adressen und Kontakten" width="420">
</picture>

```php
// config/filament-relation-navigator.php
'global_search_actions' => 3, // true für alle, oder die maximale Anzahl Links pro Ergebnis
```

```php
// Auf einer Resource: nur für diese Resource an oder aus
public static function hasRelationPageGlobalSearchActions(): bool
{
    return true;
}
```

```php
// An einem Relation Manager (mit InteractsWithRelationPage): nie verlinkt
protected static bool $hasRelationPageGlobalSearchAction = false;
```

Resources mit `HasRelationPages` bekommen die Links von selbst. Eine Resource,
die `getGlobalSearchResultActions()` selbst definiert oder nur über ihre
Relation Manager aktiviert ist, fügt sie so hinzu:

```php
public static function getGlobalSearchResultActions(Model $record): array
{
    return [
        // ...deine Aktionen
        ...RelationNavigator::getGlobalSearchResultActions(static::class, $record),
    ];
}
```

Die Links folgen denselben Regeln wie das Menü: nur die Relationsseiten, die
der Benutzer öffnen darf.
<!-- #endregion global-search -->

## Menü-Presets

<!-- #region menu-presets -->
Veröffentliche die Config:

```bash
php artisan vendor:publish --tag=filament-relation-navigator-config
```

```php
'menu' => [
    'preset' => 'github',   // 'simple', ein eigenes Preset oder ein View-Name
    'presets' => [
        'simple' => 'filament-relation-navigator::menu.simple',
        'github' => 'filament-relation-navigator::menu.github',
    ],
    'searchable' => true,   // ein Suchfeld über der Liste, bei jedem Preset
    'search_from' => 0,     // ...ab dieser Anzahl Einträge
    'badges' => 'lazy',     // die Badges der Relation Manager: 'lazy' beim Öffnen des Menüs, true mit der Seite, false aus
    'max_height' => '24rem',
    'separator' => true,    // Datensatzseiten und Relationsseiten in zwei Blöcken
    'mobile' => true,       // das Menü über der Überschrift auf kleinen Bildschirmen
    'shortcut' => 'alt+shift+r', // öffnet das Menü; null schaltet es ab
],
```

**github** (Standard): die letzte Breadcrumb bleibt Text, und ein Pfeil daneben
öffnet ein Panel mit Überschrift, Suchfeld und einem Häkchen bei der aktuellen
Seite, wie der Repository-Umschalter von GitHub. Das Feld bekommt beim Öffnen
den Fokus, und Enter öffnet den ersten Treffer.

**simple**: die letzte Breadcrumb selbst öffnet die Liste. Für das Menü von
1.2 und früher setze `'preset' => 'simple'` und `'searchable' => false`.

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/menu-simple-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/menu-simple-light.png" alt="Das Preset simple auf der Ansichtsseite" width="520">
</picture>

### Badges

Das Menü zeigt die Badges der Relation Manager, wie in ihren Tabs.
Standardmäßig werden sie **beim Öffnen des Menüs geladen**: bis dahin ist das
Menü geschlossen, also wird die Seite ohne ihre Queries gerendert, auch bei
jedem Livewire-Request, der die Breadcrumbs neu zeichnet. Für den Moment, den
sie brauchen, erscheint ein Spinner.

```php
'badges' => 'lazy', // Standard; true: mit der Seite; false: keine Badges
```

Schalte sie ab, wo nötig:

```php
// Eine Seite oder eine Resource (statisch): keine Badges in diesem Menü
public function hasRelationNavigatorBadges(): bool
{
    return false;
}

// Ein Relation Manager (mit InteractsWithRelationPage): sein Badge bleibt
// im Tab, nicht im Menü
protected static bool $hasRelationNavigatorBadge = false;
```

Ein Relation Manager, dessen Tab-Badge in Filament lazy ist
(`protected static ?bool $isBadgeLazyLoaded = true;`, oder `$isBadgeDeferred`
in Filament 4.13), ist auch im Menü lazy, selbst mit `'badges' => true`.

Menü-Views, die Badges nicht nachladen können (vor 1.3 veröffentlichte Views
des Packages oder eigene Presets, die `$item->badge` selbst rendern), bekommen
sie wie bisher mit der Seite: nichts verschwindet.

**Tastatur**: vom Suchfeld aus springt Pfeil nach unten in die Liste; Pfeil
nach oben und unten wechseln zwischen den Seiten, Enter öffnet eine, Escape
schließt das Menü.

### Tastenkürzel und Tooltip

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/tooltip-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/tooltip-light.png" alt="Der Tooltip des Menü-Buttons mit seinem Tastenkürzel" width="470">
</picture>

Der Button, der das Menü öffnet, hat einen Tooltip mit dem Tastenkürzel,
angezeigt mit Mac- oder Windows/Linux-Tasten. Das Kürzel öffnet das Menü von
überall auf der Seite, mit bereitem Suchfeld, wie der Repository-Umschalter
von GitHub: <kbd>⌥</kbd> <kbd>⇧</kbd> <kbd>R</kbd> auf dem Mac,
<kbd>Alt</kbd> <kbd>Shift</kbd> <kbd>R</kbd> anderswo. Ändere es in der Config
oder pro Seite:

```php
'shortcut' => 'mod+shift+p', // mod ist ⌘ auf dem Mac und sonst Ctrl
```

```php
public function getRelationNavigatorShortcut(): ?string
{
    return null; // kein Tastenkürzel auf dieser Seite
}
```

Es ist so gebaut, dass es nicht stört:

- es erkennt die physische Taste, funktioniert also mit jedem Tastaturlayout
  und mit Option auf dem Mac (wo <kbd>⌥</kbd><kbd>⇧</kbd><kbd>R</kbd> ‰ tippt);
- es tut nichts, während du in einem Feld tippst;
- ein Tastenkürzel der globalen Suche des Panels (`globalSearchKeyBindings()`)
  wird nie übernommen: das Menü hat dann kein Kürzel;
- nur das sichtbare Menü öffnet sich (Breadcrumbs oder kleine Bildschirme).

Unterstützte Tasten: `alt`, `shift`, `ctrl`, `meta`, `mod` plus ein Buchstabe,
eine Ziffer oder `/ . , ;`.

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/menu-github-search-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/menu-github-search-light.png" alt="Das Preset github mit dem Suchfeld" width="700">
</picture>

### Seite, Resource oder Config

Jede Option hat drei Ebenen. Die Seite gewinnt, dann die Resource, dann die
Config:

| Methode der Seite (statisch auf der Resource) | Config-Schlüssel |
|---|---|
| `getRelationNavigatorPreset(): string` | `menu.preset` |
| `isRelationNavigatorSearchable(): bool` | `menu.searchable` |
| `hasRelationNavigatorBadges(): bool` | `menu.badges` |
| `hasDeferredRelationNavigatorBadges(): bool` | `menu.badges` = `'lazy'` |
| `getRelationNavigatorShortcut(): ?string` | `menu.shortcut` |
| `hasRelationNavigatorMobileMenu(): bool` | `menu.mobile` |
| `hasRelationNavigatorRecordNavigation(): bool` | `record_navigation` |
| `shouldRelationNavigatorRecordNavigationFollowList(): bool` | `record_navigation_follows_list` |
| `shouldRedirectRelationNavigatorTabs(): bool` | `redirect_relation_tabs` |

```php
// Auf einer Seite
public function isRelationNavigatorSearchable(): bool
{
    return false;
}
```

```php
// Auf einer Resource: dieselbe Methode, statisch, für alle ihre Seiten
public static function hasRelationNavigatorRecordNavigation(): bool
{
    return true;
}
```

### Kleine Bildschirme

Filament blendet die Breadcrumbs unter 40rem aus. Dort erscheint das Menü
stattdessen über der Seitenüberschrift, auf jeder Seite mit
`HasRelationNavigator`, mit denselben Preset-Optionen. Schalte es mit
`'mobile' => false` ab, oder pro Seite:

```php
public function hasRelationNavigatorMobileMenu(): bool
{
    return false;
}
```

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/mobile-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/mobile-light.png" alt="Das Menü auf einem Smartphone, über der Seitenüberschrift" width="320">
</picture>

### Übersetzungen

32 Sprachen, mit denselben Codes wie Filament: Arabisch, Bulgarisch,
Chinesisch (vereinfacht und traditionell), Dänisch, Deutsch, Englisch,
Finnisch, Französisch, Griechisch, Hebräisch, Indonesisch, Italienisch,
Japanisch, Katalanisch, Koreanisch, Niederländisch, Norwegisch, Persisch,
Polnisch, Portugiesisch (Portugal und Brasilien), Rumänisch, Russisch,
Schwedisch, Slowakisch, Spanisch, Tschechisch, Türkisch, Ukrainisch, Ungarisch
und Vietnamesisch. In Sprachen, die von rechts nach links laufen, drehen sich
die Pfeile für vorherigen und nächsten Datensatz um. Veröffentliche die
Übersetzungen, um sie zu ändern oder eine Sprache hinzuzufügen:

```bash
php artisan vendor:publish --tag=filament-relation-navigator-translations
```

### Ein eigenes Preset

Starte mit einem der Presets des Packages:

```bash
php artisan make:filament-relation-navigator-preset compact --from=github
```

Der Befehl kopiert das Preset nach
`resources/views/filament/relation-navigator/compact.blade.php`. Registriere es
und passe es nach Belieben an:

```php
'preset' => 'compact',
'presets' => [
    // ...
    'compact' => 'filament.relation-navigator.compact',
],
```

Jeder View-Name funktioniert auch als `preset`. Um die Presets des Packages
selbst zu ändern, veröffentliche ihre Views:

```bash
php artisan vendor:publish --tag=filament-relation-navigator-views
```

Eine Preset-View bekommt:

| Variable             | Inhalt                                                                  |
| -------------------- | ----------------------------------------------------------------------- |
| `$label`             | Die letzte Breadcrumb                                                   |
| `$items`             | Die Menüeinträge: `key`, `label`, `url`, `icon`, `badge`, `badgeColor`, `isActive` |
| `$heading`           | "Switch page", übersetzt                                                |
| `$isSearchable`      | Ob das Suchfeld angezeigt wird                                          |
| `$hasSeparator`      | Ob Datensatzseiten und Relationsseiten in zwei Blöcke geteilt werden    |
| `$shortcut`          | Das Tastenkürzel oder null                                              |
| `$shortcutKeys`      | Die anzuzeigenden Tasten: `['mac' => [...], 'other' => [...]]`          |
| `$searchPlaceholder` | "Find a page…", übersetzt                                               |
| `$emptyLabel`        | "No pages found.", übersetzt                                            |
| `$maxHeight`         | `menu.max_height`                                                       |

Um deinem Button Tooltip und Tastenkürzel zu geben, binde
`filament-relation-navigator::menu.partials.trigger-attributes` in seinem Tag
ein. Jeder Eintrag hat außerdem `isRelationPage()`. Um das Suchfeld, die Liste
(mit Trenner und Tastaturnavigation) und die Zeile „keine Ergebnisse“
wiederzuverwenden, binde `filament-relation-navigator::menu.partials.list` mit
`showCheck` ein.
<!-- #endregion menu-presets -->

## Relationsseiten anpassen

<!-- #region customising-the-relation-pages -->
### Titel, Untertitel und Breadcrumb einer Relation

Ein Relation Manager kann sie für seine eigene Seite festlegen, ausgehend vom
Datensatz, ohne Seitenklasse:

```php
class OrdersRelationManager extends RelationManager
{
    public static function getRelationPageTitle(Model $ownerRecord): string|Htmlable|null
    {
        return "Bestellungen von {$ownerRecord->name}";
    }

    public static function getRelationPageSubheading(Model $ownerRecord): string|Htmlable|null
    {
        return 'Letzte 12 Monate';
    }

    public static function getRelationPageBreadcrumb(Model $ownerRecord): ?string
    {
        return 'Bestellungen';
    }
}
```

Gib `null` zurück, um den Standardwert zu behalten. Eine eigene Seitenklasse
(unten) gewinnt mit ihren eigenen `$title`, `$subheading` und `$breadcrumb`.
Das Menü und die Tabs behalten den Titel des Relation Managers.
Relationsgruppen haben keinen Relation Manager, daher nutzt ihre Seite das
Label der Gruppe.

### Eine eigene Seitenklasse

Die generierten Relationsseiten decken die meisten Fälle ab. Um ihren Titel,
ihre Breadcrumbs oder alles andere zu ändern, was eine Filament-Seite kann,
schreib die Seitenklasse selbst; das Package nutzt dann deine für diese
Resource:

```bash
php artisan make:filament-relation-navigator CustomerResource
```

```php
class CustomerRelationPage extends RelationPage
{
    protected static string $resource = CustomerResource::class;

    protected static ?array $relations = ['orders', 'invoices'];  // nur diese
    protected static array $excludedRelations = ['notes'];         // nie diese
}
```

```php
public static function getPages(): array
{
    return [
        'index' => ListCustomers::route('/'),
        'view' => ViewCustomer::route('/{record}'),
        ...CustomerRelationPage::relationRoutes(),
    ];
}
```

Die Klasse ist eine normale Filament-Seite: überschreibe `getTitle()`,
`getBreadcrumbs()`, `getHeaderActions()`, nutze deine eigenen Traits und so
weiter.
<!-- #endregion customising-the-relation-pages -->

## Native Sub-Navigation des Datensatzes

<!-- #region native-record-sub-navigation -->
Dieselben Einträge können die Sub-Navigation des Datensatzes in Filament
füllen:

```php
public static function getRecordSubNavigation(Page $page): array
{
    return RelationNavigator::getRecordSubNavigation($page);
}
```
<!-- #endregion native-record-sub-navigation -->

## So funktioniert es

<!-- #region how-it-works -->
- Die Routen werden über Filaments eigenes `Resource::registerRoutes()`
  registriert, innerhalb der Routengruppe jeder Resource: Slug, Cluster,
  übergeordnete Resource und Tenant übernimmt Filament.
- Die Seitenklasse einer Resource wird bei Bedarf nach
  `storage/framework/cache` geschrieben, so wie Laravels Real-Time Facades
  arbeiten, und bei Livewire registriert. Das funktioniert mit `route:cache`.
- Die Relationsseite bettet den Relation Manager so ein, wie Filament es in
  den Tabs tut. Zustand, Aktionen und Modals bleiben also im Relation Manager.
<!-- #endregion how-it-works -->

## KI-Agenten

<!-- #region ai-agents -->
Das Package bringt KI-Coding-Agenten bei, wie man es benutzt, damit Claude
Code, Codex, Cursor, Copilot und ähnliche die Traits und Optionen richtig
schreiben:

- **Laravel Boost**: das Package liefert Guidelines und einen Skill
  (`relation-navigator-development`) mit. Wähle `wit3/filament-relation-navigator`,
  wenn du `php artisan boost:install` (oder `boost:update`) ausführst.
- **llms.txt**: die Dokumentation wird auch für Agenten veröffentlicht, als
  Index ([llms.txt](https://wit3.github.io/filament-relation-navigator/llms.txt))
  und als einzelne Datei
  ([llms-full.txt](https://wit3.github.io/filament-relation-navigator/llms-full.txt)),
  auf Englisch. Jede Seite gibt es auch als Markdown-Version: hänge `.md` an
  ihre URL an.
- **Ohne Boost**: verweise deinen Agenten auf
  `vendor/wit3/filament-relation-navigator/resources/boost/skills/relation-navigator-development/SKILL.md`,
  zum Beispiel aus deiner `AGENTS.md` oder `CLAUDE.md`.
<!-- #endregion ai-agents -->

## Tests

<!-- #region testing -->
```bash
composer test        # die Testsuite
composer analyse     # PHPStan, Level 8
composer format      # Pint
```

Die Suite läuft auf Filament 4 und 5, Laravel 12 und 13, PHP 8.2 bis 8.5 und
deckt verschachtelte Resources, Cluster, Tenancy und den SPA-Modus ab.

Die Browsertests (das Menü mit Suche) brauchen Playwright:

```bash
npm install && npx playwright install chromium
vendor/bin/testbench filament:assets
composer test:browser
```
<!-- #endregion testing -->

## Changelog

<!-- #region changelog -->
Siehe [CHANGELOG](https://github.com/wit3/filament-relation-navigator/blob/main/CHANGELOG.md) (auf Englisch).
<!-- #endregion changelog -->

## Mitwirken

<!-- #region contributing -->
Issues und Pull Requests sind willkommen. Bitte führe `composer test`,
`composer analyse` und `composer format` aus, bevor du einen Pull Request
öffnest.
<!-- #endregion contributing -->

## Sicherheit

<!-- #region security -->
Melde Sicherheitsprobleme bitte privat über
[GitHub Security Advisories](https://github.com/wit3/filament-relation-navigator/security/advisories/new),
nicht in öffentlichen Issues.
<!-- #endregion security -->

## Credits

<!-- #region credits -->
- [Salvatore Scalzi](https://github.com/wit3)
- [Alle Mitwirkenden](https://github.com/wit3/filament-relation-navigator/contributors)
<!-- #endregion credits -->

## Lizenz

<!-- #region license -->
Die MIT-Lizenz (MIT). Siehe [LICENSE](https://github.com/wit3/filament-relation-navigator/blob/main/LICENSE.md).
<!-- #endregion license -->
