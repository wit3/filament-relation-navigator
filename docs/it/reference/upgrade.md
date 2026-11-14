# Guida all'aggiornamento

Il package segue il [versionamento semantico](https://semver.org/lang/it/): le versioni minor e patch non rompono mai il tuo codice. Qui trovi cosa guardare quando aggiorni. Il [changelog](/it/reference/changelog) ha ogni modifica.

```bash
composer update wit3/filament-relation-navigator
php artisan filament:assets
```

## Alla 1.7

Niente da cambiare. Quando la navigazione tra record è attiva, **anche le resource annidate mostrano precedente e successivo**, dentro lo stesso record genitore, nell'ordine della relation manager del genitore quando la resource annidata è la sua related resource (`$relatedResource`). Per nasconderli su una resource annidata, restituisci `false` da `hasRelationNavigatorRecordNavigation()` sulle sue pagine o sulla resource.

## Alla 1.6

Niente da cambiare.

- Dalla 1.6.1 l'impianto interno del package è marcato `@internal` (vedi il changelog): se chiami `RelationNavigator::optsIn()`, `registerRoutes()` o funzioni simili, passa a quello che elenca il [riferimento API](/it/reference/api).
- Dalla 1.6.1 la versione minima di Filament è la 4.13.

- **I badge del menu arrivano quando apri il menu**, non con la pagina. Per il comportamento di prima: `'badges' => true`. Le viste del menu pubblicate prima della 1.3, e i preset tuoi, continuano a riceverli con la pagina.
- L'azione «Apri come pagina» ha nuove opzioni (`relation_manager_action`) e nuovi agganci: vedi [Apri come pagina](/it/guide/open-as-page).

## Alla 1.5

Niente da cambiare. Due comportamenti sono diversi:

- **Precedente e successivo seguono la lista da cui arrivi** (ordinamento, filtri, ricerca, scheda) invece dell'ordine delle chiavi, quando la navigazione è attiva. Per tenere l'ordine delle chiavi: `'record_navigation_follows_list' => false`.
- **«Apri come pagina»** apre la pagina propria di un relation manager prima di quella di un gruppo a cui appartiene.

Se hai pubblicato le viste del package, ripubblica `menu/partials/list.blade.php`: i relation manager con il badge della scheda lazy ora lo caricano così anche nel menu.

## Alla 1.4

Niente da cambiare.

- Il link **«Apri come pagina»** ora è un'azione nell'intestazione della tabella del relation manager invece di un link sopra. Se vuoi spegnerlo da qualche parte, vedi [Apri come pagina](/it/guide/open-as-page): config, resource o relation manager.
- `RelationNavigator::renderRelationManagerLink()` è deprecato: il package non lo usa più. Funziona ancora per un tuo render hook e verrà tolto nella 2.0.
- Ogni opzione del menu ora si può impostare anche su una resource: vedi [Pagina, resource o config](/it/guide/menu-presets#pagina-resource-o-config).

## Alla 1.3

::: danger Correzione di sicurezza
Le versioni dalla 1.0.0 alla 1.2.1 non applicavano la policy `view` del record alle pagine delle relazioni ([GHSA-v3mh-w7qp-37gc](https://github.com/wit3/filament-relation-navigator/security/advisories/GHSA-v3mh-w7qp-37gc)). Se usi le policy sui record, aggiorna.
:::

- **Il menu predefinito ora è il preset `github` con il campo di ricerca.** Se hai pubblicato la config, tieni quello che hai impostato. Per tenere il menu della 1.2:

  ```php
  // config/filament-relation-navigator.php
  'menu' => [
      'preset' => 'simple',
      'searchable' => false,
  ],
  ```

- Il menu non elenca più la pagina di modifica a chi non può modificare il record.
- Nuove opzioni, tutte con un valore predefinito nel codice: una config pubblicata dalla 1.2 continua a funzionare. Aggiungile quando vuoi cambiarle: `relation_manager_link`, `record_navigation`, `redirect_relation_tabs` e `'lazy'` per `menu.badges`.
- Se hai pubblicato le viste del package e vuoi `'badges' => 'lazy'`, ripubblica `menu/partials/list.blade.php`.

## Alla 1.2

- `HasRelationNavigator` ora dichiara `getRecord()` e `hasRecord()`: una pagina senza record fallisce appena la classe viene caricata, invece che dopo. Le pagine di visualizzazione e modifica e quelle che usano `InteractsWithRecord` li hanno già.
