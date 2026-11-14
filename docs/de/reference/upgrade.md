# Upgrade-Anleitung

Das Package folgt der [semantischen Versionierung](https://semver.org): Minor- und Patch-Releases machen deinen Code nie kaputt. Diese Seite zeigt, worauf du beim Update achten solltest. Der [Changelog](/de/reference/changelog) enthält jede Änderung.

```bash
composer update wit3/filament-relation-navigator
php artisan filament:assets
```

## Auf 1.7

Nichts zu ändern. Wenn die Datensatznavigation aktiv ist, **zeigen auch verschachtelte Resources vorherigen und nächsten Datensatz**, innerhalb desselben übergeordneten Datensatzes, in der Reihenfolge des Relation Managers der übergeordneten Resource, wenn die verschachtelte Resource seine Related Resource ist (`$relatedResource`). Um sie auf einer verschachtelten Resource auszublenden, gib `false` aus `hasRelationNavigatorRecordNavigation()` auf ihren Seiten oder auf der Resource zurück.

## Auf 1.6

Nichts zu ändern.

- Ab 1.6.1 sind die internen Bausteine des Packages als `@internal` markiert (siehe Changelog): wenn du `RelationNavigator::optsIn()`, `registerRoutes()` oder ähnliche Hilfsmethoden aufrufst, wechsle zu dem, was die [API-Referenz](/de/reference/api) aufführt.
- Ab 1.6.1 ist Filament 4.13 die niedrigste unterstützte Version.

- **Die Badges im Menü werden beim Öffnen des Menüs geladen**, nicht mit der Seite. Für das alte Verhalten: `'badges' => true`. Menü-Views, die vor 1.3 veröffentlicht wurden, und eigene Presets bekommen sie weiter mit der Seite.
- Die Aktion „Als Seite öffnen“ hat neue Optionen (`relation_manager_action`) und Hooks: siehe [Als Seite öffnen](/de/guide/open-as-page).

## Auf 1.5

Nichts zu ändern. Zwei Verhaltensweisen sind anders:

- **Vorheriger und nächster Datensatz folgen der Liste, von der du kommst** (Sortierung, Filter, Suche, Tab) statt der Reihenfolge der Schlüssel, wenn die Datensatznavigation aktiv ist. Um die Reihenfolge der Schlüssel zu behalten: `'record_navigation_follows_list' => false`.
- **„Als Seite öffnen“** öffnet die eigene Seite eines Relation Managers vor der Seite einer Relationsgruppe, zu der er auch gehört.

Wenn du die Views des Packages veröffentlicht hast, veröffentliche `menu/partials/list.blade.php` erneut: Relation Manager mit lazy Tab-Badge laden es jetzt auch im Menü so.

## Auf 1.4

Nichts zu ändern.

- Der Link **„Als Seite öffnen“** ist jetzt eine Aktion im Header der Tabelle des Relation Managers statt eines Links darüber. Wenn du ihn irgendwo abschalten willst, siehe [Als Seite öffnen](/de/guide/open-as-page): Config, Resource oder Relation Manager.
- `RelationNavigator::renderRelationManagerLink()` ist veraltet: das Package nutzt es nicht mehr. Es funktioniert weiter für einen eigenen Render Hook und wird in 2.0 entfernt.
- Jede Menü-Option lässt sich jetzt auch auf einer Resource setzen: siehe [Seite, Resource oder Config](/de/guide/menu-presets#seite-resource-oder-config).

## Auf 1.3

::: danger Sicherheitskorrektur
Die Versionen 1.0.0 bis 1.2.1 wendeten die `view`-Policy des Datensatzes nicht auf Relationsseiten an ([GHSA-v3mh-w7qp-37gc](https://github.com/wit3/filament-relation-navigator/security/advisories/GHSA-v3mh-w7qp-37gc)). Wenn du Policies für deine Datensätze nutzt, aktualisiere.
:::

- **Das Standardmenü ist jetzt das Preset `github` mit Suchfeld.** Wenn du die Config veröffentlicht hast, behältst du, was du dort gesetzt hast. Um das Menü von 1.2 zu behalten:

  ```php
  // config/filament-relation-navigator.php
  'menu' => [
      'preset' => 'simple',
      'searchable' => false,
  ],
  ```

- Das Menü zeigt die Bearbeitungsseite nicht mehr Benutzern, die den Datensatz nicht bearbeiten dürfen.
- Neue Optionen, alle mit Standardwerten im Code: eine ab 1.2 veröffentlichte Config funktioniert weiter. Ergänze sie in deiner Config, wenn du sie ändern willst: `relation_manager_link`, `record_navigation`, `redirect_relation_tabs` und `'lazy'` für `menu.badges`.
- Wenn du die Views des Packages veröffentlicht hast und `'badges' => 'lazy'` willst, veröffentliche `menu/partials/list.blade.php` erneut.

## Auf 1.2

- `HasRelationNavigator` deklariert jetzt `getRecord()` und `hasRecord()`: eine Seite ohne Datensatz schlägt fehl, sobald ihre Klasse geladen wird, statt später. Ansichts- und Bearbeitungsseiten und Seiten mit `InteractsWithRecord` haben sie bereits.
