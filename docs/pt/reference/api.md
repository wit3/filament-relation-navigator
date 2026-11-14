# Referência da API

Tudo o que está abaixo é API pública, coberta pelo [versionamento semântico](/pt/reference/upgrade): não muda de forma incompatível dentro da 1.x. O que não está listado aqui é interno.

## Traits

| Trait | Onde usar | O que faz |
|---|---|---|
| `Concerns\HasRelationPages` | um resource | Cada relation manager ganha sua própria página |
| `Concerns\InteractsWithRelationPage` | um relation manager | Este relation manager ganha uma página; lê as propriedades static opcionais abaixo |
| `Concerns\HasRelationNavigator` | uma página do registro (visualização, edição, personalizada) | O menu no breadcrumb, e a página aparece nele |

Todos no namespace `Wit3\FilamentRelationNavigator`.

## Em um resource

Todos static, todos opcionais.

| Método | Para que serve |
|---|---|
| `hasRelationPage(string $slug): bool` | Deixar uma relação de fora |
| `canAccessRelationPage(Model $record, string $slug): bool` | Vetar uma página de relação para um registro (páginas, menu, pesquisa global, redirecionamento) |
| `relationPageHeader(Schema $schema): Schema` | Cabeçalho acima da relação em todas as páginas de relação |
| `getRelationPageHeaderActions(): array` | Ações de cabeçalho das páginas de relação |
| `getRelationPageHeaderWidgets(): array`, `getRelationPageFooterWidgets(): array` | Widgets das páginas de relação (recebem `$record`) |
| `hasRelationPageLinks(): bool` | "Abrir como página" nos seus relation managers |
| `configureRelationPageAction(Action $action, RelationManager $manager): Action` | Mudar essa ação para este resource |
| `hasRelationPageGlobalSearchActions(): bool` | Links da pesquisa global para este resource |
| qualquer [método de opção](/pt/reference/configuration#opcoes), static | A opção para todas as páginas do resource |

## Em um relation manager

Todos static, todos opcionais. As propriedades exigem `InteractsWithRelationPage`; os métodos funcionam sem ele.

| Propriedade ou método | Para que serve |
|---|---|
| `$hasRelationPage`, `hasRelationPage(): bool` | Uma página para este relation manager ou não |
| `$relationPageSlug`, `getRelationPageSlug(): ?string` | O slug da página dele |
| `$hasRelationPageLink`, `hasRelationPageLink(): bool` | "Abrir como página" na tabela dele |
| `$hasRelationPageGlobalSearchAction`, `hasRelationPageGlobalSearchAction(): bool` | O link dele abaixo dos resultados da pesquisa global |
| `$hasRelationNavigatorBadge`, `hasRelationNavigatorBadge(): bool` | O badge dele no menu (a aba mantém o seu) |
| `getRelationPageTitle(Model $ownerRecord)` | Título da página dele; `null` mantém o padrão |
| `getRelationPageSubheading(Model $ownerRecord)` | Subtítulo da página dele |
| `getRelationPageBreadcrumb(Model $ownerRecord): ?string` | Último item do breadcrumb da página dele |
| `relationPageHeader()`, `getRelationPageHeaderActions()`, `getRelationPageHeaderWidgets()`, `getRelationPageFooterWidgets()` | Como no resource, só para a página dele |
| `configureRelationPageAction(Action $action, RelationManager $manager): Action` | Mudar a ação "Abrir como página" dele |

O `isBadgeLazyLoaded()` do próprio Filament (ou `$isBadgeLazyLoaded`) também deixa o badge dele no menu lazy.

## Em uma página do registro (`HasRelationNavigator`)

Sobrescreva qualquer um deles na página:

| Método | Para que serve |
|---|---|
| os [métodos de opção](/pt/reference/configuration#opcoes) | As opções desta página |
| `getRelationNavigatorRecordNavigationQuery(): ?Builder` | Os registros que anterior e próximo percorrem |
| `withRelationNavigator(array $breadcrumbs): array` | Adicionar o menu a breadcrumbs que você mesmo monta |

## Uma classe de página de relação sua

Estenda `Pages\RelationPage` (ou rode `php artisan make:filament-relation-navigator CustomerResource`) e espalhe `...CustomerRelationPage::relationRoutes()` em `getPages()`.

| Membro | Para que serve |
|---|---|
| `protected static ?array $relations` | Só estes slugs |
| `protected static array $excludedRelations` | Nunca estes slugs |
| `protected static ?string $contextPage` | Chave da página que ela representa (padrão: a página de visualização, senão a de edição) |
| `$title`, `$subheading`, `$breadcrumb` e todos os métodos de página do Filament | Como em qualquer página do Filament |

## Helpers

Métodos static de `RelationNavigator`:

| Método | Para que serve |
|---|---|
| `getItems(string $resource, Model $record, ...)` | Os itens do menu de um registro |
| `getRecordSubNavigation(Page $page): array` | Os mesmos itens como subnavegação do Filament |
| `getRelationManagerPageUrl(RelationManager $manager): ?string` | A página de um relation manager, para botões seus |
| `getGlobalSearchResultActions(string $resource, Model $record): array` | Links da pesquisa global, para juntar aos seus |
| `configureRelationPageActionUsing(Closure $callback): void` | Mudar todas as ações "Abrir como página" |
| `makeRelationPageAction(RelationManager $manager): Action` | Montar essa ação |
| `getRelationPageClass(string $resource): ?string` | A classe de página de relação de um resource |
| `RELATION_PAGE_ACTION` | Nome da ação "Abrir como página" |

`RelationNavigatorItem` (em `$items`): `key`, `label`, `url`, `icon`, `badge`, `badgeColor`, `isActive`, `isBadgeDeferred`, `isRelationPage()`.

## Rotas, views e comandos

- Nomes das rotas: `{base das rotas do resource}.relations.{slug}`, para `getUrl('relations.orders', [...])`.
- Views: `filament-relation-navigator::menu.github`, `menu.simple`, `menu.mobile`, `menu.partials.list`, `menu.partials.trigger-attributes`. As variáveis delas estão em [Um preset só seu](/pt/guide/menu-presets#um-preset-so-seu).
- Comandos: `make:filament-relation-navigator {resource}`, `make:filament-relation-navigator-preset {nome} --from=github|simple`.
- Traduções: `filament-relation-navigator::navigator.*`.
