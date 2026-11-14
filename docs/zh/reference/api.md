# API 参考

下面列出的都是公开 API，遵循[语义化版本](/zh/reference/upgrade)：在 1.x 中不会发生不兼容的变更。未在此列出的内容都属于内部实现。

## Trait

| Trait | 用在 | 作用 |
|---|---|---|
| `Concerns\HasRelationPages` | resource | 每个 relation manager 都有自己的页面 |
| `Concerns\InteractsWithRelationPage` | relation manager | 此 relation manager 拥有页面；读取下面可选的静态属性 |
| `Concerns\HasRelationNavigator` | 记录页面（查看、编辑、自定义） | 面包屑菜单，并且该页面会列在菜单中 |

都位于 `Wit3\FilamentRelationNavigator` 命名空间下。

## 在 resource 上

全部是静态的，全部可选。

| 方法 | 用途 |
|---|---|
| `hasRelationPage(string $slug): bool` | 排除某个关联 |
| `canAccessRelationPage(Model $record, string $slug): bool` | 按记录否决某个关联页面（页面、菜单、全局搜索、重定向） |
| `relationPageHeader(Schema $schema): Schema` | 所有关联页面上关联上方的页头 |
| `getRelationPageHeaderActions(): array` | 关联页面的页头操作 |
| `getRelationPageHeaderWidgets(): array`、`getRelationPageFooterWidgets(): array` | 关联页面的 widget（会接收 `$record`） |
| `hasRelationPageLinks(): bool` | 其 relation manager 上的“作为页面打开” |
| `configureRelationPageAction(Action $action, RelationManager $manager): Action` | 为此 resource 修改该操作 |
| `hasRelationPageGlobalSearchActions(): bool` | 此 resource 的全局搜索链接 |
| 任何[选项方法](/zh/reference/configuration#选项)，static | 该选项作用于 resource 的所有页面 |

## 在 relation manager 上

全部是静态的，全部可选。属性需要 `InteractsWithRelationPage`；方法不需要。

| 属性或方法 | 用途 |
|---|---|
| `$hasRelationPage`、`hasRelationPage(): bool` | 此 relation manager 是否拥有页面 |
| `$relationPageSlug`、`getRelationPageSlug(): ?string` | 其页面的 slug |
| `$hasRelationPageLink`、`hasRelationPageLink(): bool` | 其表格上的“作为页面打开” |
| `$hasRelationPageGlobalSearchAction`、`hasRelationPageGlobalSearchAction(): bool` | 它在全局搜索结果下的链接 |
| `$hasRelationNavigatorBadge`、`hasRelationNavigatorBadge(): bool` | 它在菜单中的徽章（标签页保留自己的徽章） |
| `getRelationPageTitle(Model $ownerRecord)` | 其页面的标题；`null` 保留默认值 |
| `getRelationPageSubheading(Model $ownerRecord)` | 其页面的副标题 |
| `getRelationPageBreadcrumb(Model $ownerRecord): ?string` | 其页面面包屑的最后一项 |
| `relationPageHeader()`、`getRelationPageHeaderActions()`、`getRelationPageHeaderWidgets()`、`getRelationPageFooterWidgets()` | 与 resource 上的相同，只作用于它自己的页面 |
| `configureRelationPageAction(Action $action, RelationManager $manager): Action` | 修改它的“作为页面打开”操作 |

Filament 自己的 `isBadgeLazyLoaded()`（或 `$isBadgeLazyLoaded`）也会让它在菜单中的徽章变为 lazy。

## 在记录页面上（`HasRelationNavigator`）

可以在页面上重写其中任何一个：

| 方法 | 用途 |
|---|---|
| [选项方法](/zh/reference/configuration#选项) | 此页面的选项 |
| `getRelationNavigatorRecordNavigationQuery(): ?Builder` | 上一条和下一条所遍历的记录 |
| `withRelationNavigator(array $breadcrumbs): array` | 把菜单添加到你自己构建的面包屑中 |

## 自己编写关联页面类

继承 `Pages\RelationPage`（或运行 `php artisan make:filament-relation-navigator CustomerResource`），并把 `...CustomerRelationPage::relationRoutes()` 展开到 `getPages()` 中。

| 成员 | 用途 |
|---|---|
| `protected static ?array $relations` | 只包括这些 slug |
| `protected static array $excludedRelations` | 永远不包括这些 slug |
| `protected static ?string $contextPage` | 它所代表的页面的键（默认：查看页面，否则为编辑页面） |
| `$title`、`$subheading`、`$breadcrumb`，以及所有 Filament 页面方法 | 与任何 Filament 页面相同 |

## 辅助方法

`RelationNavigator` 的静态方法：

| 方法 | 用途 |
|---|---|
| `getItems(string $resource, Model $record, ...)` | 某条记录的菜单项 |
| `getRecordSubNavigation(Page $page): array` | 同样的菜单项，作为 Filament 子导航 |
| `getRelationManagerPageUrl(RelationManager $manager): ?string` | relation manager 的页面，用于你自己的按钮 |
| `getGlobalSearchResultActions(string $resource, Model $record): array` | 全局搜索链接，用于合并到你自己的链接中 |
| `configureRelationPageActionUsing(Closure $callback): void` | 修改所有“作为页面打开”操作 |
| `makeRelationPageAction(RelationManager $manager): Action` | 构建该操作 |
| `getRelationPageClass(string $resource): ?string` | resource 的关联页面类 |
| `RELATION_PAGE_ACTION` | “作为页面打开”操作的名称 |

`RelationNavigatorItem`（在 `$items` 中）：`key`、`label`、`url`、`icon`、`badge`、`badgeColor`、`isActive`、`isBadgeDeferred`、`isRelationPage()`。

## 路由、视图和命令

- 路由名称：`{resource 路由前缀}.relations.{slug}`，用于 `getUrl('relations.orders', [...])`。
- 视图：`filament-relation-navigator::menu.github`、`menu.simple`、`menu.mobile`、`menu.partials.list`、`menu.partials.trigger-attributes`。它们的变量列在[自定义预设](/zh/guide/menu-presets#自定义预设)中。
- 命令：`make:filament-relation-navigator {resource}`、`make:filament-relation-navigator-preset {名称} --from=github|simple`。
- 翻译：`filament-relation-navigator::navigator.*`。
