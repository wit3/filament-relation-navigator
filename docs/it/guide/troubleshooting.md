# Problemi comuni

## Il menu non compare

- La pagina deve usare `HasRelationNavigator`. Le pagine delle relazioni lo hanno sempre.
- Il menu richiede almeno due voci: la pagina stessa e un'altra.
- Una pagina che costruisce le sue breadcrumb deve restituire `$this->withRelationNavigator([...])`.
- Sotto i 40rem Filament nasconde le breadcrumb: il menu va sopra il titolo (`menu.mobile`).

## Il menu ha un aspetto strano o la freccina è fuori posto

Ripubblica il foglio di stile del plugin, dopo ogni aggiornamento:

```bash
php artisan filament:assets
```

## Una pagina di relazione dà 404

- Il relation manager non ha una pagina: la resource deve usare `HasRelationPages`, o il relation manager `InteractsWithRelationPage`, e non deve escludersi.
- Il suo slug coincide con una chiave di pagina della resource (`edit`, `view`...): questi slug vengono saltati. Scegline un altro con `$relationPageSlug`.
- Con le rotte in cache, rigenerale dopo un deploy: `php artisan route:cache`.
- La policy nega il record «come non trovato» (`Response::denyAsNotFound()`): il 404 è voluto.

## Una pagina di relazione dà 403

Una pagina di relazione richiede il `canAccess()` della pagina di visualizzazione, la policy `view` del record (`update` per una pagina di modifica), il `canViewForRecord()` del relation manager e, se l'hai definito, il `canAccessRelationPage()` della resource. Controllali in quest'ordine.

## I badge non compaiono

- `menu.badges` è `false`, oppure la pagina o la resource li spengono, o il relation manager (`hasRelationNavigatorBadge()`).
- Di default arrivano quando apri il menu, con uno spinner. Se lo spinner non si ferma, guarda nella console del browser se una richiesta Livewire è fallita.
- Le viste pubblicate prima della 1.3 non sanno caricare i badge dopo: li ricevono con la pagina.

## La scorciatoia non fa niente

- Non fa niente mentre scrivi in un campo.
- Una scorciatoia della ricerca globale del panel (`globalSearchKeyBindings()`) non viene mai presa: scegline un'altra con `menu.shortcut`.
- Un'estensione del browser o il sistema operativo potrebbero usare gli stessi tasti.

## Manca «Apri come pagina»

- `relation_manager_link` è `false`, oppure la resource o il relation manager la spengono.
- Il relation manager non ha una pagina propria, o ci sei già sopra.
- Una tabella con una vista `->header()` personalizzata non ha posto per le azioni d'intestazione.

## Mancano precedente e successivo

- Sono spenti di default: `record_navigation`.
- Spariscono quando non c'è un altro record (su una resource annidata, nessun altro record dello stesso genitore).
- Per seguire l'ordine della lista, la lista deve essere stata aperta in questa sessione, con la navigazione attiva nella config o sulla resource.

## «A directory that only this user can write»

Lo storage è in sola lettura, quindi le classi generate vanno nella cartella temporanea di sistema, e quella cartella appartiene a qualcun altro. Rendi scrivibile `storage/framework/cache`, rimuovi la cartella indicata nel messaggio, o scegline una in un service provider:

```php
GeneratedRelationPages::useCachePath(storage_path('app/relation-pages'));
```

## Ancora bloccato

Chiedi nelle [GitHub Discussions](https://github.com/wit3/filament-relation-navigator/discussions), o apri una [issue](https://github.com/wit3/filament-relation-navigator/issues) con la tua versione di Filament e poche righe che riproducono il problema.
