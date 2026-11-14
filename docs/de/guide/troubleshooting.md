# Fehlerbehebung

## Das Menü erscheint nicht

- Die Seite muss `HasRelationNavigator` nutzen. Relationsseiten haben es immer.
- Das Menü braucht mindestens zwei Einträge: die Seite selbst und einen weiteren.
- Eine Seite, die ihre Breadcrumbs selbst baut, muss `$this->withRelationNavigator([...])` zurückgeben.
- Unter 40rem blendet Filament die Breadcrumbs aus: das Menü wandert über die Überschrift (`menu.mobile`).

## Das Menü sieht falsch aus oder der Pfeil sitzt falsch

Veröffentliche das Stylesheet des Plugins nach jedem Update erneut:

```bash
php artisan filament:assets
```

## Eine Relationsseite gibt 404

- Der Relation Manager hat keine Seite: die Resource braucht `HasRelationPages` oder der Relation Manager `InteractsWithRelationPage`, und er darf sich nicht ausschließen.
- Sein Slug entspricht einem Seitenschlüssel der Resource (`edit`, `view`...): solche Slugs werden übersprungen. Wähle einen anderen mit `$relationPageSlug`.
- Mit gecachten Routen: cache sie nach einem Deploy erneut, `php artisan route:cache`.
- Die Policy lehnt den Datensatz mit „nicht gefunden“ ab (`Response::denyAsNotFound()`): dann ist der 404 gewollt.

## Eine Relationsseite gibt 403

Eine Relationsseite braucht das `canAccess()` der Ansichtsseite, die `view`-Policy des Datensatzes (`update` für eine Bearbeitungsseite), das `canViewForRecord()` des Relation Managers und, falls du es definiert hast, das `canAccessRelationPage()` der Resource. Prüfe sie in dieser Reihenfolge.

## Die Badges erscheinen nicht

- `menu.badges` ist `false`, die Seite oder die Resource schaltet sie ab, oder der Relation Manager tut es (`hasRelationNavigatorBadge()`).
- Standardmäßig werden sie beim Öffnen des Menüs geladen, mit einem Spinner. Wenn der Spinner nicht aufhört, such in der Browserkonsole nach einem fehlgeschlagenen Livewire-Request.
- Views, die vor 1.3 veröffentlicht wurden, können Badges nicht nachladen: sie bekommen sie stattdessen mit der Seite.

## Das Tastenkürzel tut nichts

- Es tut nichts, während du in einem Feld tippst.
- Ein Tastenkürzel der globalen Suche des Panels (`globalSearchKeyBindings()`) wird nie übernommen: wähle ein anderes mit `menu.shortcut`.
- Eine Browsererweiterung oder das Betriebssystem nutzt vielleicht dieselben Tasten.

## „Als Seite öffnen“ fehlt

- `relation_manager_link` ist `false`, oder die Resource oder der Relation Manager schaltet die Aktion ab.
- Der Relation Manager hat keine eigene Seite, oder du bist schon darauf.
- Eine Tabelle mit eigener `->header()`-View hat keinen Platz für Header-Aktionen.

## Vorheriger und nächster Datensatz fehlen

- Sie sind standardmäßig aus: `record_navigation`.
- Sie verschwinden, wenn es keinen anderen Datensatz gibt (bei einer verschachtelten Resource: keinen anderen Datensatz desselben übergeordneten Datensatzes).
- Um der Reihenfolge der Liste zu folgen, muss die Liste in dieser Session geöffnet worden sein, mit aktiver Datensatznavigation in der Config oder auf der Resource.

## „A directory that only this user can write“

Der Storage ist schreibgeschützt, also landen die generierten Seitenklassen im temporären Verzeichnis des Systems, und dieses Verzeichnis gehört jemand anderem. Mach `storage/framework/cache` beschreibbar, entferne das in der Meldung genannte Verzeichnis oder wähle eines in einem Service Provider:

```php
GeneratedRelationPages::useCachePath(storage_path('app/relation-pages'));
```

## Immer noch nicht gelöst

Frag in den [GitHub Discussions](https://github.com/wit3/filament-relation-navigator/discussions) oder öffne ein [Issue](https://github.com/wit3/filament-relation-navigator/issues) mit deiner Filament-Version und ein paar Zeilen, die das Problem nachstellen.
