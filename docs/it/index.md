---
layout: home

hero:
  name: Relation Navigator
  text: Una pagina vera per ogni relation manager
  tagline: Ogni relation manager ha la sua pagina e il suo URL. Un menu con ricerca nella breadcrumb passa dall'una all'altra. Per Filament 4 e 5.
  image:
    light: https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-light.png
    dark: https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-dark.png
    alt: Gli ordini di un cliente nella loro pagina, con il menu della breadcrumb aperto
  actions:
    - theme: brand
      text: Inizia
      link: /it/guide/quick-start
    - theme: alt
      text: Vedi su GitHub
      link: https://github.com/wit3/filament-relation-navigator

features:
  - title: Un trait, nessuna classe pagina
    details: Aggiungi HasRelationPages alla resource. Niente da generare, niente da aggiungere a getPages() o al panel provider.
  - title: Il relation manager che hai già
    details: Stessa tabella, filtri, azioni, badge e permessi, su una rotta tutta sua da linkare, salvare e aprire in una nuova scheda.
  - title: Un menu nella breadcrumb
    details: Tutte le pagine del record, con icone, badge, ricerca, tastiera e scorciatoia. Due preset o una vista tutta tua.
  - title: Testata, azioni e widget
    details: La scheda del record, widget e azioni di testata sopra la relazione, presi dalla resource.
  - title: Record precedente e successivo
    details: Scorri i record restando sulla stessa pagina, nell'ordine della lista da cui arrivi.
  - title: Accendi e spegni tutto, ovunque
    details: Ogni funzione si accende o si spegne a livello globale, per resource e per pagina o relation manager.
---

<div class="home-section">

## Da scheda a pagina

Le schede sotto la pagina di visualizzazione diventano pagine vere. Stesso relation manager, stessa tabella, un URL da condividere:

| Prima | Dopo |
| --- | --- |
| `/customers/1?relation=orders` | `/customers/1/orders` |
| `/customers/1?relation=invoices` | `/customers/1/invoices` |

Bastano due trait: vedi l'[avvio rapido](/it/guide/quick-start).

<img class="demo" src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/demo.gif" alt="Il menu aperto con Alt Maiusc R, filtrato su Invoices, poi il cliente successivo sulla stessa pagina">

</div>

<style>
.home-section { max-width: 1152px; margin: 64px auto 0; padding: 0 24px; }
.home-section h2 { border-top: 0; margin-top: 0; padding-top: 0; }
.home-section .demo { width: 100%; margin-top: 24px; border-radius: 12px; border: 1px solid var(--vp-c-divider); }
</style>
