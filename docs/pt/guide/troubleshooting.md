# Solução de problemas

## O menu não aparece

- A página precisa usar `HasRelationNavigator`. As páginas das relações sempre usam.
- O menu precisa de pelo menos dois itens: a própria página e mais um.
- Uma página que monta os próprios breadcrumbs precisa retornar `$this->withRelationNavigator([...])`.
- Abaixo de 40rem o Filament esconde os breadcrumbs: o menu vai para cima do título (`menu.mobile`).

## O menu está com aparência errada ou a setinha está fora do lugar

Publique de novo a folha de estilo do plugin, depois de cada atualização:

```bash
php artisan filament:assets
```

## Uma página de relação dá 404

- O relation manager não tem página: o resource precisa de `HasRelationPages`, ou o relation manager de `InteractsWithRelationPage`, e ele não pode estar excluído.
- O slug dele é igual a uma chave de página do resource (`edit`, `view`...): esses slugs são ignorados. Escolha outro com `$relationPageSlug`.
- Com as rotas em cache, gere o cache de novo depois de um deploy: `php artisan route:cache`.
- A policy nega o registro "como não encontrado" (`Response::denyAsNotFound()`): o 404 é proposital.

## Uma página de relação dá 403

Uma página de relação exige o `canAccess()` da página de visualização, a policy `view` do registro (`update` para uma página de edição), o `canViewForRecord()` do relation manager e, se você o definiu, o `canAccessRelationPage()` do resource. Verifique nessa ordem.

## Os badges não aparecem

- `menu.badges` é `false`, a página ou o resource os desliga, ou o relation manager desliga (`hasRelationNavigatorBadge()`).
- Por padrão eles carregam quando o menu abre, com um spinner. Se o spinner não para, veja no console do navegador se uma requisição do Livewire falhou.
- Views publicadas antes da 1.3 não conseguem carregar os badges depois: elas os recebem com a página.

## O atalho não faz nada

- Ele não faz nada enquanto você digita em um campo.
- Um atalho da pesquisa global do painel (`globalSearchKeyBindings()`) nunca é tomado: escolha outro com `menu.shortcut`.
- Uma extensão do navegador ou o sistema operacional pode usar as mesmas teclas.

## "Abrir como página" não aparece

- `relation_manager_link` é `false`, ou o resource ou o relation manager a desliga.
- O relation manager não tem página própria, ou você já está nela.
- Uma tabela com uma view `->header()` personalizada não tem lugar para ações de cabeçalho.

## Anterior e próximo não aparecem

- Eles vêm desligados por padrão: `record_navigation`.
- Eles somem quando não há outro registro (em um resource aninhado, nenhum outro registro do mesmo pai).
- Para seguir a ordem da lista, a lista precisa ter sido aberta nesta sessão, com a navegação entre registros ligada na config ou no resource.

## "A directory that only this user can write"

O storage é somente leitura, então as classes de página geradas vão para o diretório temporário do sistema, e esse diretório pertence a outro usuário. Torne `storage/framework/cache` gravável, remova o diretório indicado na mensagem ou escolha um em um service provider:

```php
GeneratedRelationPages::useCachePath(storage_path('app/relation-pages'));
```

## Ainda com problemas

Pergunte nas [GitHub Discussions](https://github.com/wit3/filament-relation-navigator/discussions) ou abra uma [issue](https://github.com/wit3/filament-relation-navigator/issues) com a sua versão do Filament e algumas linhas que reproduzam o problema.
