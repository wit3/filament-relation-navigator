# Guia de atualização

O pacote segue o [versionamento semântico](https://semver.org): versões minor e patch nunca quebram o seu código. Esta página lista o que você pode querer conferir ao atualizar. O [changelog](/pt/reference/changelog) tem todas as mudanças.

```bash
composer update wit3/filament-relation-navigator
php artisan filament:assets
```

## Para a 1.7

Nada a mudar. Quando a navegação entre registros está ligada, **os resources aninhados também mostram anterior e próximo**, dentro do mesmo registro pai, na ordem do relation manager do pai quando o resource aninhado é o related resource dele (`$relatedResource`). Para escondê-los em um resource aninhado, retorne `false` de `hasRelationNavigatorRecordNavigation()` nas páginas dele ou no resource.

## Para a 1.6

Nada a mudar.

- A partir da 1.6.1, a estrutura interna do pacote está marcada como `@internal` (veja o changelog): se você chama `RelationNavigator::optsIn()`, `registerRoutes()` ou helpers parecidos, passe a usar o que a [referência da API](/pt/reference/api) lista.
- A partir da 1.6.1, a versão mínima do Filament é a 4.13.

- **Os badges do menu são carregados quando o menu abre**, não com a página. Para o comportamento antigo: `'badges' => true`. Views do menu publicadas antes da 1.3, e presets seus, continuam recebendo os badges com a página.
- A ação "Abrir como página" tem novas opções (`relation_manager_action`) e hooks: veja [Abrir como página](/pt/guide/open-as-page).

## Para a 1.5

Nada a mudar. Dois comportamentos são diferentes:

- **Anterior e próximo seguem a lista de onde você veio** (ordenação, filtros, pesquisa, aba) em vez da ordem das chaves, quando a navegação entre registros está ligada. Para manter a ordem das chaves: `'record_navigation_follows_list' => false`.
- **"Abrir como página"** abre a página própria de um relation manager antes da página de um grupo de relações ao qual ele também pertence.

Se você publicou as views do pacote, publique `menu/partials/list.blade.php` de novo: relation managers com o badge da aba lazy agora também o carregam assim no menu.

## Para a 1.4

Nada a mudar.

- O link **"Abrir como página"** agora é uma ação no cabeçalho da tabela do relation manager, em vez de um link acima dela. Se quiser desligá-lo em algum lugar, veja [Abrir como página](/pt/guide/open-as-page): config, resource ou relation manager.
- `RelationNavigator::renderRelationManagerLink()` está obsoleto: o pacote não o usa mais. Ele ainda funciona em um render hook seu e será removido na 2.0.
- Toda opção do menu agora também pode ser definida em um resource: veja [Página, resource ou config](/pt/guide/menu-presets#pagina-resource-ou-config).

## Para a 1.3

::: danger Correção de segurança
As versões 1.0.0 a 1.2.1 não aplicavam a policy `view` do registro nas páginas das relações ([GHSA-v3mh-w7qp-37gc](https://github.com/wit3/filament-relation-navigator/security/advisories/GHSA-v3mh-w7qp-37gc)). Se você usa policies nos seus registros, atualize.
:::

- **O menu padrão agora é o preset `github` com o campo de pesquisa.** Se você publicou a config, mantém o que definiu. Para manter o menu da 1.2:

  ```php
  // config/filament-relation-navigator.php
  'menu' => [
      'preset' => 'simple',
      'searchable' => false,
  ],
  ```

- O menu não lista mais a página de edição para usuários que não podem editar o registro.
- Novas opções, todas com valores padrão no código: uma config publicada a partir da 1.2 continua funcionando. Adicione-as à sua config quando quiser mudá-las: `relation_manager_link`, `record_navigation`, `redirect_relation_tabs` e `'lazy'` para `menu.badges`.
- Se você publicou as views do pacote e quer `'badges' => 'lazy'`, publique `menu/partials/list.blade.php` de novo.

## Para a 1.2

- `HasRelationNavigator` agora declara `getRecord()` e `hasRecord()`: uma página sem registro falha assim que a classe é carregada, em vez de mais tarde. As páginas de visualização e de edição e as páginas que usam `InteractsWithRecord` já têm esses métodos.
