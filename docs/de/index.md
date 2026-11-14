---
layout: home

hero:
  name: Relation Navigator
  text: Eine echte Seite für jeden Relation Manager
  tagline: Jeder Relation Manager bekommt seine eigene Seite und URL. Ein durchsuchbares Menü in der Breadcrumb wechselt zwischen ihnen. Für Filament 4 und 5.
  image:
    light: https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-light.png
    dark: https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-dark.png
    alt: Die Bestellungen eines Kunden auf ihrer eigenen Seite, mit geöffnetem Breadcrumb-Menü
  actions:
    - theme: brand
      text: Loslegen
      link: /de/guide/quick-start
    - theme: alt
      text: Auf GitHub ansehen
      link: https://github.com/wit3/filament-relation-navigator

features:
  - title: Ein Trait, keine Seitenklassen
    details: Füge HasRelationPages zur Resource hinzu. Nichts zu generieren, nichts in getPages() oder im Panel Provider einzutragen.
  - title: Der Relation Manager, den du schon hast
    details: Dieselbe Tabelle, Filter, Aktionen, Badges und Berechtigungen, auf einer eigenen Route, die du verlinken, als Lesezeichen speichern und in einem neuen Tab öffnen kannst.
  - title: Ein Menü in der Breadcrumb
    details: Alle Seiten des Datensatzes, mit Icons, Badges, Suche, Tastaturnavigation und Tastenkürzel. Zwei Presets oder eine eigene View.
  - title: Header, Aktionen und Widgets
    details: Die Karte des Datensatzes, Widgets und Header-Aktionen über der Relation, übernommen aus der Resource.
  - title: Vorheriger und nächster Datensatz
    details: Blättere durch die Datensätze auf derselben Seite, in der Reihenfolge der Liste, von der du kommst.
  - title: Alles schaltbar, überall
    details: Jede Funktion lässt sich global, pro Resource und pro Seite oder Relation Manager an- und abschalten.
---

<div class="home-section">

## Vom Tab zur Seite

Die Tabs unter der Ansichtsseite werden zu eigenen Seiten. Derselbe Relation Manager, dieselbe Tabelle, eine URL zum Teilen:

| Vorher | Nachher |
| --- | --- |
| `/customers/1?relation=orders` | `/customers/1/orders` |
| `/customers/1?relation=invoices` | `/customers/1/invoices` |

Zwei Traits genügen: siehe den [Schnellstart](/de/guide/quick-start).

<img class="demo" src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/demo.gif" alt="Das Menü, geöffnet mit Alt Umschalt R, gefiltert auf Invoices, dann der nächste Kunde auf derselben Seite">

</div>

<style>
.home-section { max-width: 1152px; margin: 64px auto 0; padding: 0 24px; }
.home-section h2 { border-top: 0; margin-top: 0; padding-top: 0; }
.home-section .demo { width: 100%; margin-top: 24px; border-radius: 12px; border: 1px solid var(--vp-c-divider); }
</style>
