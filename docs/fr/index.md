---
layout: home

hero:
  name: Relation Navigator
  text: Une vraie page pour chaque relation manager
  tagline: Chaque relation manager a sa propre page et son URL. Un menu avec recherche dans le fil d'Ariane passe de l'une à l'autre. Pour Filament 4 et 5.
  image:
    light: https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-light.png
    dark: https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-dark.png
    alt: Les commandes d'un client sur leur propre page, avec le menu du fil d'Ariane ouvert
  actions:
    - theme: brand
      text: Commencer
      link: /fr/guide/quick-start
    - theme: alt
      text: Voir sur GitHub
      link: https://github.com/wit3/filament-relation-navigator

features:
  - title: Un trait, aucune classe de page
    details: Ajoutez HasRelationPages à la ressource. Rien à générer, rien à ajouter à getPages() ni au panel provider.
  - title: Le relation manager que vous avez déjà
    details: Même table, mêmes filtres, actions, badges et autorisations, sur une route à lui que vous pouvez partager, ajouter aux favoris et ouvrir dans un nouvel onglet.
  - title: Un menu dans le fil d'Ariane
    details: Toutes les pages de l'enregistrement, avec icônes, badges, recherche, navigation au clavier et raccourci. Deux presets ou une vue à vous.
  - title: En-tête, actions et widgets
    details: La fiche de l'enregistrement, des widgets et des actions d'en-tête au-dessus de la relation, repris de la ressource.
  - title: Enregistrement précédent et suivant
    details: Parcourez les enregistrements sans quitter la page, dans l'ordre de la liste d'où vous venez.
  - title: Tout s'active, partout
    details: Chaque fonction s'active ou se désactive globalement, par ressource et par page ou relation manager.
---

<div class="home-section">

## D'un onglet à une page

Les onglets sous la page d'affichage deviennent des pages à part entière. Même relation manager, même table, une URL à partager :

| Avant | Après |
| --- | --- |
| `/customers/1?relation=orders` | `/customers/1/orders` |
| `/customers/1?relation=invoices` | `/customers/1/invoices` |

Deux traits suffisent : voir le [démarrage rapide](/fr/guide/quick-start).

<img class="demo" src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/demo.gif" alt="Le menu ouvert avec Alt Maj R, filtré sur Invoices, puis le client suivant sur la même page">

</div>

<style>
.home-section { max-width: 1152px; margin: 64px auto 0; padding: 0 24px; }
.home-section h2 { border-top: 0; margin-top: 0; padding-top: 0; }
.home-section .demo { width: 100%; margin-top: 24px; border-radius: 12px; border: 1px solid var(--vp-c-divider); }
</style>
