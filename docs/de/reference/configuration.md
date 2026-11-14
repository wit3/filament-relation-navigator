# Konfiguration

Veröffentliche die Config-Datei, um die Standardwerte für alle Resources zu ändern:

```bash
php artisan vendor:publish --tag=filament-relation-navigator-config
```

Eine veröffentlichte Config funktioniert über Minor-Versionen hinweg weiter: jeder Schlüssel hat einen Standardwert im Code, neue Schlüssel musst du also nur ergänzen, wenn du sie ändern willst.

## Optionen

Die meisten Optionen lassen sich auch **pro Resource** (eine statische Methode auf der Resource) oder **pro Seite oder Relation Manager** setzen:

- **Menü-Optionen** (`menu.*` mit einer Methode, Datensatznavigation, Tab-Weiterleitung): die Seite gewinnt, dann die Resource, dann die Config.
- **„Als Seite öffnen“** und **das Menü-Badge eines Relation Managers**: erscheinen nur, wenn jede Ebene es erlaubt; jede Ebene kann sie abschalten.
- **Links der globalen Suche**: die Methode der Resource ersetzt die Config für diese Resource; ein Relation Manager kann sich nur selbst ausschließen.
- **Die gemerkte Liste für vorherigen und nächsten Datensatz**: entscheiden nur die Resource oder die Config, da die Listenseite keine Datensatzseite fragen kann.
- `menu.presets`, `menu.search_from`, `menu.max_height` und `menu.separator` gibt es nur in der Config.

| Schlüssel | Standard | Was er tut | Weitere Ebenen |
|---|---|---|---|
| `menu.preset` | `'github'` | Das Aussehen des Menüs: `github`, `simple`, ein eigenes Preset oder ein View-Name | `getRelationNavigatorPreset()` |
| `menu.presets` | `simple`, `github` | Namen und Views der Presets | |
| `menu.searchable` | `true` | Ein Suchfeld über der Liste | `isRelationNavigatorSearchable()` |
| `menu.search_from` | `0` | Das Suchfeld ab dieser Anzahl Einträge zeigen | |
| `menu.badges` | `'lazy'` | `'lazy'`: Badges laden beim Öffnen des Menüs; `true`: mit der Seite; `false`: keine | `hasRelationNavigatorBadges()`, `hasDeferredRelationNavigatorBadges()`; Relation Manager: `hasRelationNavigatorBadge()` |
| `menu.max_height` | `'24rem'` | Höhe der Liste, bevor sie scrollt (eine CSS-Länge) | |
| `menu.separator` | `true` | Datensatzseiten und Relationsseiten in zwei Blöcken | |
| `menu.mobile` | `true` | Das Menü über der Überschrift auf kleinen Bildschirmen | `hasRelationNavigatorMobileMenu()` |
| `menu.shortcut` | `'alt+shift+r'` | Tastenkürzel, das das Menü öffnet; `null`: keines | `getRelationNavigatorShortcut()` |
| `relation_manager_link` | `true` | Die Aktion „Als Seite öffnen“ an Relation-Manager-Tabellen | Resource `hasRelationPageLinks()`, Relation Manager `hasRelationPageLink()` |
| `relation_manager_action` | Link mit Pfeil | Aussehen dieser Aktion: `style`, `label`, `icon`, `icon_position`, `color`, `size`, `new_tab` | `configureRelationPageActionUsing()`, `configureRelationPageAction()` |
| `global_search_actions` | `false` | Links auf Relationsseiten unter den Ergebnissen der globalen Suche: `true` oder die maximale Anzahl pro Ergebnis | Resource `hasRelationPageGlobalSearchActions()`, Relation Manager `hasRelationPageGlobalSearchAction()` |
| `record_navigation` | `false` | Buttons für vorherigen und nächsten Datensatz | `hasRelationNavigatorRecordNavigation()` |
| `record_navigation_follows_list` | `true` | Vorheriger und nächster folgen der Liste, von der der Benutzer kommt | `shouldRelationNavigatorRecordNavigationFollowList()` |
| `redirect_relation_tabs` | `false` | `?relation=x` auf der Ansichtsseite öffnet die Relationsseite | `shouldRedirectRelationNavigatorTabs()` |

Seitenmethoden sind Instanzmethoden auf einer Datensatzseite mit `HasRelationNavigator`; auf einer Resource ist dieselbe Methode statisch und gilt für alle ihre Seiten. Siehe [Seite, Resource oder Config](/de/guide/menu-presets#seite-resource-oder-config).

## Die ganze Datei

So, wie sie mit der aktuellen Version ausgeliefert wird (die Kommentare sind auf Englisch):

<<< ../../../config/filament-relation-navigator.php
