# Configuração

Publique o arquivo de config para mudar os valores padrão de todos os resources:

```bash
php artisan vendor:publish --tag=filament-relation-navigator-config
```

Uma config publicada continua funcionando nas versões minor: cada chave tem um valor padrão no código, então as chaves novas só precisam ser adicionadas quando você quiser mudá-las.

## Opções

A maioria das opções também pode ser definida **por resource** (um método static no resource) ou **por página ou relation manager**:

- **Opções do menu** (`menu.*` com um método, navegação entre registros, redirecionamento das abas): vale primeiro a página, depois o resource, depois a config.
- **"Abrir como página"** e **o badge de um relation manager no menu**: aparecem só quando todos os níveis permitem; qualquer nível pode desligá-los.
- **Links da pesquisa global**: o método do resource substitui a config para aquele resource; um relation manager só pode se excluir.
- **A lista guardada para anterior e próximo**: só o resource ou a config decidem, já que a página da lista não tem como perguntar a uma página do registro.
- `menu.presets`, `menu.search_from`, `menu.max_height` e `menu.separator` existem só na config.

| Chave | Padrão | O que faz | Outros níveis |
|---|---|---|---|
| `menu.preset` | `'github'` | A aparência do menu: `github`, `simple`, um preset seu ou o nome de uma view | `getRelationNavigatorPreset()` |
| `menu.presets` | `simple`, `github` | Nomes e views dos presets | |
| `menu.searchable` | `true` | Um campo de pesquisa acima da lista | `isRelationNavigatorSearchable()` |
| `menu.search_from` | `0` | Mostra o campo de pesquisa a partir desta quantidade de itens | |
| `menu.badges` | `'lazy'` | `'lazy'`: os badges carregam quando o menu abre; `true`: com a página; `false`: nenhum | `hasRelationNavigatorBadges()`, `hasDeferredRelationNavigatorBadges()`; relation manager: `hasRelationNavigatorBadge()` |
| `menu.max_height` | `'24rem'` | Altura da lista antes de rolar (um comprimento CSS) | |
| `menu.separator` | `true` | Páginas do registro e páginas das relações em dois blocos | |
| `menu.mobile` | `true` | O menu acima do título em telas pequenas | `hasRelationNavigatorMobileMenu()` |
| `menu.shortcut` | `'alt+shift+r'` | Atalho de teclado que abre o menu; `null`: nenhum | `getRelationNavigatorShortcut()` |
| `relation_manager_link` | `true` | A ação "Abrir como página" nas tabelas dos relation managers | resource `hasRelationPageLinks()`, relation manager `hasRelationPageLink()` |
| `relation_manager_action` | link com seta | Aparência dessa ação: `style`, `label`, `icon`, `icon_position`, `color`, `size`, `new_tab` | `configureRelationPageActionUsing()`, `configureRelationPageAction()` |
| `global_search_actions` | `false` | Links para as páginas das relações abaixo dos resultados da pesquisa global: `true` ou o máximo por resultado | resource `hasRelationPageGlobalSearchActions()`, relation manager `hasRelationPageGlobalSearchAction()` |
| `record_navigation` | `false` | Botões de registro anterior e próximo | `hasRelationNavigatorRecordNavigation()` |
| `record_navigation_follows_list` | `true` | Anterior e próximo seguem a lista de onde o usuário veio | `shouldRelationNavigatorRecordNavigationFollowList()` |
| `redirect_relation_tabs` | `false` | `?relation=x` na página de visualização abre a página da relação | `shouldRedirectRelationNavigatorTabs()` |

Os métodos de página são métodos de instância em uma página do registro que usa `HasRelationNavigator`; em um resource o mesmo método é static e vale para todas as páginas dele. Veja [Página, resource ou config](/pt/guide/menu-presets#pagina-resource-ou-config).

## O arquivo completo

Como vem na versão atual (os comentários estão em inglês):

<<< ../../../config/filament-relation-navigator.php
