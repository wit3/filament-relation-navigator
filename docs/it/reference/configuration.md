# Configurazione

Pubblica il file di config per cambiare i valori predefiniti di tutte le resource:

```bash
php artisan vendor:publish --tag=filament-relation-navigator-config
```

Una config pubblicata continua a funzionare nelle versioni minor: ogni chiave ha un valore predefinito nel codice, quindi le chiavi nuove vanno aggiunte solo quando vuoi cambiarle.

## Opzioni

Quasi tutte le opzioni si possono impostare anche **per resource** (un metodo statico sulla resource) o **per pagina o relation manager**:

- **Opzioni del menu** (`menu.*` con un metodo, precedente/successivo, redirect delle schede): vince la pagina, poi la resource, poi la config.
- **«Apri come pagina»** e **il badge di un relation manager nel menu**: compaiono solo se tutti i livelli lo permettono; qualsiasi livello può spegnerli.
- **Link della ricerca globale**: il metodo della resource sostituisce la config per quella resource; un relation manager può solo escludersi.
- **La lista ricordata per precedente e successivo**: la decidono solo la resource o la config, perché la pagina della lista non può chiederlo a una pagina del record.
- `menu.presets`, `menu.search_from`, `menu.max_height` e `menu.separator` sono solo nella config.

| Chiave | Predefinito | Cosa fa | Altri livelli |
|---|---|---|---|
| `menu.preset` | `'github'` | L'aspetto del menu: `github`, `simple`, un tuo preset o il nome di una vista | `getRelationNavigatorPreset()` |
| `menu.presets` | `simple`, `github` | Nomi e viste dei preset | |
| `menu.searchable` | `true` | Un campo di ricerca sopra la lista | `isRelationNavigatorSearchable()` |
| `menu.search_from` | `0` | Mostra il campo di ricerca da questo numero di voci | |
| `menu.badges` | `'lazy'` | `'lazy'`: i badge arrivano all'apertura del menu; `true`: con la pagina; `false`: niente | `hasRelationNavigatorBadges()`, `hasDeferredRelationNavigatorBadges()`; relation manager: `hasRelationNavigatorBadge()` |
| `menu.max_height` | `'24rem'` | Altezza della lista prima dello scorrimento (una lunghezza CSS) | |
| `menu.separator` | `true` | Pagine del record e pagine delle relazioni in due blocchi | |
| `menu.mobile` | `true` | Il menu sopra il titolo sugli schermi piccoli | `hasRelationNavigatorMobileMenu()` |
| `menu.shortcut` | `'alt+shift+r'` | Scorciatoia da tastiera che apre il menu; `null`: nessuna | `getRelationNavigatorShortcut()` |
| `relation_manager_link` | `true` | L'azione «Apri come pagina» sulle tabelle dei relation manager | resource `hasRelationPageLinks()`, relation manager `hasRelationPageLink()` |
| `relation_manager_action` | link con freccia | Aspetto di quell'azione: `style`, `label`, `icon`, `icon_position`, `color`, `size`, `new_tab` | `configureRelationPageActionUsing()`, `configureRelationPageAction()` |
| `global_search_actions` | `false` | Link alle pagine delle relazioni sotto i risultati della ricerca globale: `true` o il massimo per risultato | resource `hasRelationPageGlobalSearchActions()`, relation manager `hasRelationPageGlobalSearchAction()` |
| `record_navigation` | `false` | Pulsanti record precedente e successivo | `hasRelationNavigatorRecordNavigation()` |
| `record_navigation_follows_list` | `true` | Precedente e successivo seguono la lista da cui arrivi | `shouldRelationNavigatorRecordNavigationFollowList()` |
| `redirect_relation_tabs` | `false` | `?relation=x` sulla pagina di visualizzazione apre la pagina della relazione | `shouldRedirectRelationNavigatorTabs()` |

I metodi di pagina sono metodi d'istanza su una pagina del record che usa `HasRelationNavigator`; su una resource lo stesso metodo è statico e vale per tutte le sue pagine. Vedi [Pagina, resource o config](/it/guide/menu-presets#pagina-resource-o-config).

## Il file completo

Come arriva con la versione attuale (i commenti sono in inglese):

<<< ../../../config/filament-relation-navigator.php
