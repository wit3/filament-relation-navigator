---
layout: home

hero:
  name: Relation Navigator
  text: Uma página de verdade para cada relation manager
  tagline: Cada relation manager ganha sua própria página e URL. Um menu com pesquisa no breadcrumb leva de uma para outra. Para Filament 4 e 5.
  image:
    light: https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-light.png
    dark: https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-dark.png
    alt: Os pedidos de um cliente na sua própria página, com o menu do breadcrumb aberto
  actions:
    - theme: brand
      text: Começar
      link: /pt/guide/quick-start
    - theme: alt
      text: Ver no GitHub
      link: https://github.com/wit3/filament-relation-navigator

features:
  - title: Um trait, nenhuma classe de página
    details: Adicione HasRelationPages ao resource. Nada para gerar, nada para adicionar em getPages() ou no panel provider.
  - title: O relation manager que você já tem
    details: Mesma tabela, filtros, ações, badges e autorização, em uma rota própria que você pode linkar, salvar nos favoritos e abrir em outra aba.
  - title: Um menu no breadcrumb
    details: Todas as páginas do registro, com ícones, badges, pesquisa, navegação por teclado e atalho. Dois presets ou uma view sua.
  - title: Cabeçalho, ações e widgets
    details: Mostre o card do registro, widgets e ações de cabeçalho acima da relação, vindos do resource.
  - title: Registro anterior e próximo
    details: Percorra os registros sem sair da página, na ordem da lista de onde você veio.
  - title: Ligue e desligue tudo, em qualquer lugar
    details: Cada recurso pode ser ligado ou desligado no geral, por resource e por página ou relation manager.
---

<div class="home-section">

## De aba para página

As abas embaixo da página de visualização viram páginas próprias. Mesmo relation manager, mesma tabela, uma URL que você pode compartilhar:

| Antes | Depois |
| --- | --- |
| `/customers/1?relation=orders` | `/customers/1/orders` |
| `/customers/1?relation=invoices` | `/customers/1/invoices` |

Bastam dois traits: veja o [início rápido](/pt/guide/quick-start).

<img class="demo" src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/demo.gif" alt="O menu aberto com Alt Shift R, filtrado em Invoices, depois o próximo cliente na mesma página">

</div>

<style>
.home-section { max-width: 1152px; margin: 64px auto 0; padding: 0 24px; }
.home-section h2 { border-top: 0; margin-top: 0; padding-top: 0; }
.home-section .demo { width: 100%; margin-top: 24px; border-radius: 12px; border: 1px solid var(--vp-c-divider); }
</style>
