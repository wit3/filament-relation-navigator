---
layout: home

hero:
  name: Relation Navigator
  text: A real page for every relation manager
  tagline: "/customers/1?relation=orders becomes /customers/1/orders, and a searchable menu in the breadcrumb jumps between them. For Filament v4 and v5."
  actions:
    - theme: brand
      text: Get started
      link: /guide/quick-start
    - theme: alt
      text: View on GitHub
      link: https://github.com/wit3/filament-relation-navigator

features:
  - title: One trait, no page classes
    details: Add HasRelationPages to the resource. Nothing to generate, nothing to add to getPages() or to the panel provider.
  - title: The relation manager you already have
    details: Same table, filters, actions, badges and authorization, on its own route you can link, bookmark and open in a new tab.
  - title: A menu in the breadcrumb
    details: Every page of the record, with icons, badges, search, keyboard navigation and a shortcut. Two presets or a view of your own.
  - title: Header, actions and widgets
    details: Show the record's card, widgets and header actions above the relation, taken from the resource.
  - title: Previous and next record
    details: Walk through the records on the same page, from a customer's invoices to the next customer's invoices.
  - title: Switch anything, anywhere
    details: Every feature can be turned on or off globally, per resource and per page or relation manager.
---

<div class="demo">
  <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/demo.gif" alt="The menu opened with Alt Shift R, filtered to Invoices, then the next customer on the same page">
</div>

<style>
.demo { max-width: 1152px; margin: 48px auto 0; padding: 0 24px; }
.demo img { width: 100%; border-radius: 12px; border: 1px solid var(--vp-c-divider); }
</style>
