# Relation Navigator per Filament

> **Documentazione:** guide, configurazione, aggiornamenti e ricerca su
> **[wit3.github.io/filament-relation-navigator/it](https://wit3.github.io/filament-relation-navigator/it/)**.
>
> **In inglese:** la [documentazione](https://wit3.github.io/filament-relation-navigator/)
> e il [README](https://github.com/wit3/filament-relation-navigator/blob/main/README.md) originale.

<!-- #region intro -->
Dai a ogni relation manager di una resource di Filament **una pagina tutta
sua**, e passa dall'una all'altra con un **menu nella breadcrumb**.

- `/customers/1?relation=orders` diventa **`/customers/1/orders`**: una rotta
  vera, da linkare, salvare nei preferiti e aprire in una nuova scheda.
- La pagina mostra **il relation manager che hai già**: stessa tabella,
  filtri, azioni, badge e permessi. Niente di duplicato.
- Sopra la relazione puoi mettere una **testata** (la scheda del record,
  widget, azioni), presa dalla resource.
- L'ultima voce della breadcrumb apre un **menu** con le pagine del record e
  tutte le relazioni che l'utente può vedere, con icone e badge. Due aspetti,
  ricerca e tastiera, una vista tutta tua e una versione per schermi piccoli.
- **Record precedente e successivo** sulla stessa pagina, un'azione **«Apri
  come pagina»** su ogni tabella di relation manager e link alle vecchie
  schede che portano alle nuove pagine: ognuno attivabile a livello globale,
  di resource o di pagina.
- **Un trait, nessuna classe pagina**: niente file da generare, niente da
  aggiungere a `getPages()` o al panel provider.

<img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/demo.gif" alt="Il menu aperto con Alt Maiusc R, filtrato su Invoices, poi il cliente successivo sulla stessa pagina" width="880">
<!-- #endregion intro -->

## Requisiti

<!-- #region requirements -->
| Pacchetto | Versione             |
| --------- | -------------------- |
| PHP       | 8.2+                 |
| Laravel   | 12, 13               |
| Filament  | 4.13+, 5.x           |
<!-- #endregion requirements -->

## Installazione

<!-- #region installation -->
```bash
composer require wit3/filament-relation-navigator
php artisan filament:assets
```

Lancia di nuovo `filament:assets` dopo ogni aggiornamento: Filament copia il
foglio di stile del plugin in `public/`.
<!-- #endregion installation -->

## Avvio rapido

<!-- #region quick-start -->
**1. Dai una pagina alle relazioni.** Aggiungi il trait alla resource:

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

Ora ogni relazione ha la sua pagina: `/customers/1/orders`,
`/customers/1/invoices`, `/customers/1/notes`.

**2. Aggiungi il menu alla pagina di visualizzazione:**

```php
use Wit3\FilamentRelationNavigator\Concerns\HasRelationNavigator;

class ViewCustomer extends ViewRecord
{
    use HasRelationNavigator;
}
```

Fatto. Le pagine delle relazioni hanno già il menu, e le schede della pagina
di visualizzazione continuano a funzionare come prima.

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-light.png" alt="Gli ordini di un cliente nella loro pagina, con il menu della breadcrumb aperto">
</picture>
<!-- #endregion quick-start -->

## Pagine delle relazioni

<!-- #region relation-pages -->
### Attivarle

Attivale sulla resource, per tutti i relation manager, oppure solo sui
relation manager che vuoi:

```php
use Wit3\FilamentRelationNavigator\Concerns\InteractsWithRelationPage;

class OrdersRelationManager extends RelationManager
{
    use InteractsWithRelationPage;
}
```

| Configurazione                              | Relazioni con una pagina            |
| ------------------------------------------- | ----------------------------------- |
| `HasRelationPages` sulla resource           | tutte, tranne quelle escluse        |
| `InteractsWithRelationPage` su un manager   | solo i manager che lo usano         |
| Una classe pagina scritta da te (sotto)     | tutte, tranne quelle escluse        |

Per escludere qualche relazione:

```php
// Sulla resource
public static function hasRelationPage(string $slug): bool
{
    return $slug !== 'notes';
}

// Su un relation manager, con InteractsWithRelationPage
protected static bool $hasRelationPage = false;
```

Un relation manager condiviso da due resource si attiva per entrambe: usa
`hasRelationPage()` della resource per escluderne una.

### URL e slug

| Voce di `getRelations()`                        | URL                         |
| ----------------------------------------------- | --------------------------- |
| `'orders' => OrdersRelationManager::class`      | `/customers/1/orders`       |
| `SupportTicketsRelationManager::class` (senza chiave) | `/customers/1/support-tickets` |
| `RelationGroup::make('Activity', [...])`        | `/customers/1/activity`     |

Vince lo slug scelto dal relation manager
(`protected static ?string $relationPageSlug = 'tickets';`), poi la chiave di
`getRelations()`, poi il nome della relazione in kebab-case (o l'etichetta del
gruppo: dai una chiave ai gruppi, perché un'etichetta tradotta cambia con la
lingua). Gli slug uguali a una chiave di pagina della resource (`edit`,
`view`...) vengono saltati, e così una seconda relazione con uno slug già
preso.

Ogni pagina è una rotta con nome, `relations.{slug}`:

```php
CustomerResource::getUrl('relations.orders', ['record' => $customer]);
```

### Permessi e comportamento

Il relation manager riceve come `$pageClass` la pagina di **visualizzazione**
della resource (quella di modifica, se non c'è), quindi `isReadOnly()`,
`canViewForRecord()`, `getBadge()` e tutti gli altri controlli funzionano
esattamente come nella scheda. La pagina di visualizzazione si trova per
chiave (`view`, `edit`, o `$contextPage` su una classe pagina scritta da te)
o per classe.

Una pagina di relazione richiede quello che richiede la pagina di
visualizzazione (il suo `canAccess()` e la policy `view` del record; `update`
quando sta per la pagina di modifica) più il `canViewForRecord()` del manager.
Senza pagina di visualizzazione o di modifica vale la policy `view`. Una
policy che nega «come non trovato» dà 404, come uno slug sconosciuto. Menu,
ricerca globale e «Apri come pagina» linkano solo le pagine che l'utente può
aprire.

Le pagine delle relazioni non eseguono il codice della pagina di
visualizzazione: i controlli nel suo `authorizeAccess()` o un
`getRelationManagers()` filtrato per utente lì non valgono. Mettili nelle
policy, in `canViewForRecord()`, oppure vieta una pagina di relazione per
record sulla resource:

```php
public static function canAccessRelationPage(Model $record, string $slug): bool
{
    return $slug !== 'audit-logs' || auth()->user()->isAdmin();
}
```
<!-- #endregion relation-pages -->

## Testata, azioni e widget

<!-- #region header-actions-and-widgets -->
Sopra la relazione, una pagina di relazione può mostrare una parte della
visualizzazione del record, azioni di testata e widget. Definiscili sulla
resource per tutte le pagine di relazione, o su un relation manager per la
sua pagina:

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-light.png" alt="Una pagina di relazione con widget, la scheda del cliente e un’azione di testata sopra gli ordini" width="880">
</picture>

```php
public static function relationPageHeader(Schema $schema): Schema
{
    // Tutta l'infolist (static::infolist($schema)) o una sua parte.
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
    return [CustomerStats::class]; // riceve il record come $record
}

public static function getRelationPageFooterWidgets(): array
{
    return [];
}
```

- La testata di un relation manager sostituisce quella della resource; azioni
  e widget di entrambi si sommano, prima quelli della resource.
- Le azioni di testata agiscono sul record a cui appartiene la relazione.
- Con una testata, il nome del record non viene ripetuto sotto il titolo.
- Quello che esiste solo dentro la classe della pagina di visualizzazione (i
  suoi metodi e le sue modali) da qui non si raggiunge: spostalo nella
  resource, in una classe di azione o in un trait.
<!-- #endregion header-actions-and-widgets -->

## Il menu nella breadcrumb

<!-- #region the-breadcrumb-menu -->
`HasRelationNavigator` vuole una pagina con un record: `ViewRecord`,
`EditRecord` e le pagine che usano `InteractsWithRecord` hanno già i due
metodi che dichiara, `getRecord()` e `hasRecord()`. Su una pagina senza
record PHP lo dice appena la classe viene caricata.

Una pagina del record è nel menu se, e solo se, usa `HasRelationNavigator`:
la pagina di visualizzazione, quella di modifica o qualsiasi pagina
personalizzata del record. Su quelle pagine l'ultima voce della breadcrumb
apre il menu. Le pagine delle relazioni sono sempre elencate, dopo quelle del
record, con le icone e i badge dei relation manager, e solo quando l'utente
può vederle.

Se la pagina costruisce già le sue breadcrumb, passale a
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

Una pagina personalizzata che usa direttamente `InteractsWithRecord`, invece
di estendere `ViewRecord` o `EditRecord`, ha due metodi `getBreadcrumbs()`.
Se non definisce il suo, di' a PHP quale vince:

```php
class CustomerActivity extends Page
{
    use HasRelationNavigator, InteractsWithRecord {
        HasRelationNavigator::getBreadcrumbs insteadof InteractsWithRecord;
        InteractsWithRecord::getBreadcrumbs as getRecordBreadcrumbs;
    }
}
```

Il menu usa `$navigationLabel` e `$navigationIcon` della pagina, e la mostra
solo quando `canAccess()` lo permette per quel record.
<!-- #endregion the-breadcrumb-menu -->

## Apri come pagina

<!-- #region open-as-page -->
Nelle pagine di visualizzazione e di modifica, ogni relation manager che ha
una sua pagina riceve un'azione **Apri come pagina** nell'intestazione della
tabella, accanto al titolo e prima delle sue azioni. Apre la pagina del
relation manager, o quella del suo gruppo se non ne ha una propria. Sulla
pagina di un gruppo l'azione compare anche sui manager che hanno una pagina
propria; sulla sua pagina un manager non punta mai a se stesso.

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/open-as-page-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/open-as-page-light.png" alt="L’azione Apri come pagina nell’intestazione della tabella Orders, sulla pagina di visualizzazione" width="880">
</picture>

Spegnila dove serve:

```php
// config/filament-relation-navigator.php: ovunque
'relation_manager_link' => false,
```

```php
// Su una resource: tutti i suoi relation manager
public static function hasRelationPageLinks(): bool
{
    return false;
}
```

```php
// Su un relation manager (con InteractsWithRelationPage)
protected static bool $hasRelationPageLink = false;

// oppure, senza il trait
public static function hasRelationPageLink(): bool
{
    return false;
}
```

### Il suo aspetto

Di default è un link con una freccia. Cambialo nella config:

```php
'relation_manager_action' => [
    'style' => 'button',        // link, button o icon_button (l'etichetta diventa un tooltip)
    'label' => 'Pagina intera', // testo o chiave di traduzione; null: «Apri come pagina», tradotto
    'icon' => 'heroicon-m-arrow-top-right-on-square', // qualsiasi icona di Filament, o null
    'icon_position' => 'after', // o before
    'color' => 'gray',
    'size' => null,             // sm, md, lg...
    'new_tab' => false,
],
```

Oppure modifica direttamente l'azione di Filament, per tutto il resto tranne
il suo URL e quando compare, che decide il package. Ogni livello riceve
l'azione come l'ha lasciata il precedente:

```php
// Un service provider: tutti i relation manager
RelationNavigator::configureRelationPageActionUsing(
    fn (Action $action, RelationManager $relationManager) => $action->outlined()->badge('nuovo'),
);
```

```php
// Una resource, o un relation manager: solo lì
public static function configureRelationPageAction(Action $action): Action
{
    return $action->iconButton()->tooltip('Apri gli ordini nella loro pagina');
}
```

`RelationNavigator::getRelationManagerPageUrl($relationManager)` restituisce
lo stesso URL, per un pulsante tuo.
<!-- #endregion open-as-page -->

## Record precedente e successivo

<!-- #region previous-and-next-record -->
Due pulsanti prima delle azioni di testata aprono il record precedente o
successivo **sulla stessa pagina**: dalle fatture di un cliente a quelle del
cliente successivo.

<img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/record-navigation.gif" alt="Record precedente e successivo: dalle fatture di un cliente a quelle del successivo, con un tooltip che nomina il record" width="880">
 Un record che non può aprire quella pagina si apre sulla
sua prima pagina del menu. Sono spenti di default, perché aggiungono qualche
query a ogni render:

```php
'record_navigation' => true,
```

Seguono **la lista da cui arrivi**: il suo ordinamento, i filtri, la ricerca e
la scheda, come a schermo (fino a 100 record; circa 30 con chiavi UUID). Dal primo o dall'ultimo record
di quella lista non si va oltre, invece di saltare a un altro ordine. Un
record che non è nella lista (aperto da un link) segue l'ordine delle
chiavi. Per seguire sempre l'ordine delle chiavi:

```php
'record_navigation_follows_list' => false,
```

La lista ricorda i suoi record solo quando la navigazione è attiva nella
config o sulla resource (`hasRelationNavigatorRecordNavigation()`), perché la
pagina della lista non può chiederlo a una pagina del record.

Usano la query della resource (`getRecordRouteBindingEloquentQuery()`),
quindi tenant e scope valgono, e saltano i record che l'utente non può
vedere. Per pagina:

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

Le resource annidate non sono ancora supportate: non mostrano i pulsanti.
<!-- #endregion previous-and-next-record -->

## Link alle schede dei relation manager

<!-- #region links-to-relation-manager-tabs -->
Quando le pagine delle relazioni sostituiscono le schede, i vecchi link come
`/customers/1?relation=orders` possono aprire `/customers/1/orders`:

```php
'redirect_relation_tabs' => true,
```

Le schede senza pagina di relazione, o che il record non può vedere,
restano dove sono.
<!-- #endregion links-to-relation-manager-tabs -->

## Ricerca globale

<!-- #region global-search -->
I risultati della ricerca globale possono portare direttamente alle pagine
delle relazioni del record: cerchi un cliente, salti ai suoi ordini. Spenta
di default, perché esegue il `canViewForRecord()` dei relation manager per
ogni risultato:

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/global-search-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/global-search-light.png" alt="Un cliente tra i risultati della ricerca globale, con i link a ordini, fatture, indirizzi e contatti" width="420">
</picture>

```php
// config/filament-relation-navigator.php
'global_search_actions' => 3, // true per tutti, o il numero massimo di link per risultato
```

```php
// Su una resource: accesa o spenta solo per questa resource
public static function hasRelationPageGlobalSearchActions(): bool
{
    return true;
}
```

```php
// Su un relation manager (con InteractsWithRelationPage): mai linkato
protected static bool $hasRelationPageGlobalSearchAction = false;
```

Le resource con `HasRelationPages` ricevono i link da sole. Una resource che
definisce già `getGlobalSearchResultActions()`, o che si attiva solo dai suoi
relation manager, li aggiunge così:

```php
public static function getGlobalSearchResultActions(Model $record): array
{
    return [
        // ...le tue azioni
        ...RelationNavigator::getGlobalSearchResultActions(static::class, $record),
    ];
}
```

I link seguono le stesse regole del menu: solo le pagine di relazione che
l'utente può aprire.
<!-- #endregion global-search -->

## Preset del menu

<!-- #region menu-presets -->
Pubblica la config:

```bash
php artisan vendor:publish --tag=filament-relation-navigator-config
```

```php
'menu' => [
    'preset' => 'github',   // 'simple', un tuo preset o il nome di una vista
    'presets' => [
        'simple' => 'filament-relation-navigator::menu.simple',
        'github' => 'filament-relation-navigator::menu.github',
    ],
    'searchable' => true,   // un campo di ricerca sopra la lista, con qualsiasi preset
    'search_from' => 0,     // ...da questo numero di voci
    'badges' => 'lazy',     // i badge dei relation manager: 'lazy' all'apertura del menu, true con la pagina, false spenti
    'max_height' => '24rem',
    'separator' => true,    // pagine del record e pagine delle relazioni in due blocchi
    'mobile' => true,       // il menu sopra il titolo sugli schermi piccoli
    'shortcut' => 'alt+shift+r', // apre il menu; null lo spegne
],
```

**github** (il predefinito): l'ultima voce della breadcrumb resta testo e una
freccina accanto apre un pannello con un titolo, un campo di ricerca e una
spunta sulla pagina corrente, come il selettore di repository di GitHub. Il
campo prende il focus all'apertura e Invio apre il primo risultato.

**simple**: è l'ultima voce della breadcrumb ad aprire la lista. Per il menu
della 1.2 e precedenti, imposta `'preset' => 'simple'` e
`'searchable' => false`.

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/menu-simple-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/menu-simple-light.png" alt="Il preset simple sulla pagina di visualizzazione" width="520">
</picture>

### Badge

Il menu mostra i badge dei relation manager, come nelle schede. Di default
**arrivano quando apri il menu**: fino ad allora è chiuso, quindi la pagina
viene disegnata senza le loro query, anche a ogni richiesta Livewire che
ridisegna la breadcrumb. Per l'attimo che servono si vede uno spinner.

```php
'badges' => 'lazy', // predefinito; true: con la pagina; false: niente badge
```

Spegnili dove serve:

```php
// Una pagina, o una resource (statico): niente badge in questo menu
public function hasRelationNavigatorBadges(): bool
{
    return false;
}

// Un relation manager (con InteractsWithRelationPage): il suo badge resta
// nella scheda, non nel menu
protected static bool $hasRelationNavigatorBadge = false;
```

Un relation manager con il badge della scheda lazy in Filament
(`protected static ?bool $isBadgeLazyLoaded = true;`, o `$isBadgeDeferred` su
Filament 4.13) è lazy anche nel menu, anche con `'badges' => true`.

Le viste del menu che non sanno caricare i badge dopo (viste del package
pubblicate prima della 1.3, o preset tuoi che mostrano `$item->badge` da soli)
li ricevono con la pagina, come prima: non sparisce niente.

**Tastiera**: dal campo di ricerca, Giù entra nella lista; Su e Giù si
spostano tra le pagine, Invio ne apre una, Esc chiude il menu.

### Scorciatoia e tooltip

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/tooltip-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/tooltip-light.png" alt="Il tooltip del pulsante del menu con la sua scorciatoia" width="470">
</picture>

Il pulsante che apre il menu ha un tooltip con la scorciatoia, mostrata con i
tasti del Mac o di Windows/Linux. La scorciatoia apre il menu da qualsiasi
punto della pagina, con il campo di ricerca pronto, come il selettore di
repository di GitHub: <kbd>⌥</kbd> <kbd>⇧</kbd> <kbd>R</kbd> su Mac,
<kbd>Alt</kbd> <kbd>Shift</kbd> <kbd>R</kbd> altrove. Cambiala nella config o
per pagina:

```php
'shortcut' => 'mod+shift+p', // mod è ⌘ su Mac e Ctrl altrove
```

```php
public function getRelationNavigatorShortcut(): ?string
{
    return null; // nessuna scorciatoia su questa pagina
}
```

È fatta per non dare fastidio:

- riconosce il tasto fisico, quindi funziona con qualsiasi layout di tastiera
  e con Option su Mac (dove <kbd>⌥</kbd><kbd>⇧</kbd><kbd>R</kbd> scrive ‰);
- non fa niente mentre scrivi in un campo;
- non prende mai una scorciatoia della ricerca globale del panel
  (`globalSearchKeyBindings()`): in quel caso il menu resta senza;
- si apre solo il menu visibile (breadcrumb o schermi piccoli).

Tasti supportati: `alt`, `shift`, `ctrl`, `meta`, `mod` più una lettera, una
cifra o `/ . , ;`.

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/menu-github-search-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/menu-github-search-light.png" alt="Il preset github con il campo di ricerca" width="700">
</picture>

### Pagina, resource o config

Ogni opzione ha tre livelli. Vince la pagina, poi la resource, poi la config:

| Metodo della pagina (statico sulla resource) | Chiave di config |
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
// Su una pagina
public function isRelationNavigatorSearchable(): bool
{
    return false;
}
```

```php
// Su una resource: lo stesso metodo, statico, per tutte le sue pagine
public static function hasRelationNavigatorRecordNavigation(): bool
{
    return true;
}
```

### Schermi piccoli

Filament nasconde le breadcrumb sotto i 40rem. Lì il menu compare sopra il
titolo della pagina, su ogni pagina che usa `HasRelationNavigator`, con le
stesse opzioni del preset. Spegnilo con `'mobile' => false`, o per pagina:

```php
public function hasRelationNavigatorMobileMenu(): bool
{
    return false;
}
```

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/mobile-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/mobile-light.png" alt="Il menu su un telefono, sopra il titolo della pagina" width="320">
</picture>

### Traduzioni

Inglese, italiano, spagnolo, tedesco, francese, portoghese brasiliano e
olandese. Pubblica le traduzioni per cambiarle o aggiungere una lingua:

```bash
php artisan vendor:publish --tag=filament-relation-navigator-translations
```

### Un preset tutto tuo

Parti da uno dei preset del package:

```bash
php artisan make:filament-relation-navigator-preset compact --from=github
```

Il comando copia il preset in
`resources/views/filament/relation-navigator/compact.blade.php`. Registralo e
modificalo come vuoi:

```php
'preset' => 'compact',
'presets' => [
    // ...
    'compact' => 'filament.relation-navigator.compact',
],
```

Anche il nome di qualsiasi vista funziona come `preset`. Per modificare i
preset del package, pubblica le loro viste:

```bash
php artisan vendor:publish --tag=filament-relation-navigator-views
```

Una vista di preset riceve:

| Variabile            | Contenuto                                                               |
| -------------------- | ----------------------------------------------------------------------- |
| `$label`             | L'ultima voce della breadcrumb                                          |
| `$items`             | Le voci del menu: `key`, `label`, `url`, `icon`, `badge`, `badgeColor`, `isActive` |
| `$heading`           | "Switch page", tradotto                                                 |
| `$isSearchable`      | Se mostrare il campo di ricerca                                         |
| `$hasSeparator`      | Se dividere pagine del record e pagine delle relazioni in due blocchi   |
| `$shortcut`          | La scorciatoia, o null                                                  |
| `$shortcutKeys`      | I tasti da mostrare: `['mac' => [...], 'other' => [...]]`              |
| `$searchPlaceholder` | "Find a page…", tradotto                                                |
| `$emptyLabel`        | "No pages found.", tradotto                                             |
| `$maxHeight`         | `menu.max_height`                                                       |

Per dare al tuo pulsante tooltip e scorciatoia, includi
`filament-relation-navigator::menu.partials.trigger-attributes` dentro il suo
tag. Ogni voce ha anche `isRelationPage()`. Per riusare il campo di ricerca, la
lista (con separatore e navigazione da tastiera) e la riga «nessun
risultato», includi `filament-relation-navigator::menu.partials.list` con
`showCheck`.
<!-- #endregion menu-presets -->

## Personalizzare le pagine delle relazioni

<!-- #region customising-the-relation-pages -->
### Titolo, sottotitolo e breadcrumb di una relazione

Un relation manager può impostarli per la sua pagina, a partire dal record,
senza classe pagina:

```php
class OrdersRelationManager extends RelationManager
{
    public static function getRelationPageTitle(Model $ownerRecord): string|Htmlable|null
    {
        return "Ordini di {$ownerRecord->name}";
    }

    public static function getRelationPageSubheading(Model $ownerRecord): string|Htmlable|null
    {
        return 'Ultimi 12 mesi';
    }

    public static function getRelationPageBreadcrumb(Model $ownerRecord): ?string
    {
        return 'Ordini';
    }
}
```

Restituisci `null` per tenere il valore predefinito. Una classe pagina scritta
da te (sotto) vince con i suoi `$title`, `$subheading` e `$breadcrumb`. Il menu
e le schede tengono il titolo del relation manager. I gruppi di relazioni non
hanno un relation manager, quindi la loro pagina usa l'etichetta del gruppo.

### Una classe pagina tutta tua

Le pagine generate bastano quasi sempre. Per cambiarne il titolo, le
breadcrumb o qualsiasi altra cosa che fa una pagina di Filament, scrivi tu la
classe; il package userà la tua per quella resource:

```bash
php artisan make:filament-relation-navigator CustomerResource
```

```php
class CustomerRelationPage extends RelationPage
{
    protected static string $resource = CustomerResource::class;

    protected static ?array $relations = ['orders', 'invoices'];  // solo queste
    protected static array $excludedRelations = ['notes'];         // mai queste
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

La classe è una normale pagina di Filament: sovrascrivi `getTitle()`,
`getBreadcrumbs()`, `getHeaderActions()`, usa i tuoi trait, e così via.
<!-- #endregion customising-the-relation-pages -->

## Sotto-navigazione nativa del record

<!-- #region native-record-sub-navigation -->
Le stesse voci possono alimentare la sotto-navigazione del record di Filament:

```php
public static function getRecordSubNavigation(Page $page): array
{
    return RelationNavigator::getRecordSubNavigation($page);
}
```
<!-- #endregion native-record-sub-navigation -->

## Come funziona

<!-- #region how-it-works -->
- Le rotte vengono registrate con `Resource::registerRoutes()` di Filament,
  dentro il gruppo di rotte di ogni resource: slug, cluster, resource padre e
  tenant li gestisce Filament.
- La classe pagina di una resource viene scritta al bisogno in
  `storage/framework/cache`, come fanno le real-time facade di Laravel, e
  registrata in Livewire. Funziona con `route:cache`.
- La pagina di relazione include il relation manager come fa Filament nelle
  schede, quindi stato, azioni e modali restano nel relation manager.
<!-- #endregion how-it-works -->

## Agenti AI

<!-- #region ai-agents -->
Il package insegna agli agenti AI come usarlo, così Claude Code, Codex,
Cursor, Copilot e simili scrivono trait e opzioni nel modo giusto:

- **Laravel Boost**: il package include linee guida e una skill
  (`relation-navigator-development`). Scegli `wit3/filament-relation-navigator`
  quando lanci `php artisan boost:install` (o `boost:update`).
- **llms.txt**: la documentazione è pubblicata anche per gli agenti, come
  indice ([llms.txt](https://wit3.github.io/filament-relation-navigator/llms.txt))
  e come file unico
  ([llms-full.txt](https://wit3.github.io/filament-relation-navigator/llms-full.txt)),
  in inglese. Ogni pagina ha anche una versione Markdown: aggiungi `.md` al
  suo URL.
- **Senza Boost**: indica al tuo agente
  `vendor/wit3/filament-relation-navigator/resources/boost/skills/relation-navigator-development/SKILL.md`,
  per esempio dal tuo `AGENTS.md` o `CLAUDE.md`.
<!-- #endregion ai-agents -->

## Test

<!-- #region testing -->
```bash
composer test        # la suite di test
composer analyse     # PHPStan, livello 8
composer format      # Pint
```

La suite gira su Filament 4 e 5, Laravel 12 e 13, PHP dalla 8.2 alla 8.5, e
copre resource annidate, cluster, tenancy e modalità SPA.

I test nel browser (il menu con la ricerca) richiedono Playwright:

```bash
npm install && npx playwright install chromium
vendor/bin/testbench filament:assets
composer test:browser
```
<!-- #endregion testing -->

## Changelog

<!-- #region changelog -->
Vedi il [CHANGELOG](https://github.com/wit3/filament-relation-navigator/blob/main/CHANGELOG.md) (in inglese).
<!-- #endregion changelog -->

## Contribuire

<!-- #region contributing -->
Issue e pull request sono benvenute. Prima di aprire una pull request lancia
`composer test`, `composer analyse` e `composer format`.
<!-- #endregion contributing -->

## Sicurezza

<!-- #region security -->
Segnala i problemi di sicurezza in privato tramite i
[GitHub security advisories](https://github.com/wit3/filament-relation-navigator/security/advisories/new),
non nelle issue pubbliche.
<!-- #endregion security -->

## Crediti

<!-- #region credits -->
- [Salvatore Scalzi](https://github.com/wit3)
- [Tutti i contributori](https://github.com/wit3/filament-relation-navigator/contributors)
<!-- #endregion credits -->

## Licenza

<!-- #region license -->
Licenza MIT. Vedi [LICENSE](https://github.com/wit3/filament-relation-navigator/blob/main/LICENSE.md).
<!-- #endregion license -->
