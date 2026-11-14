<img class="filament-hidden" src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/cover.jpg" alt="Relation Navigator for Filament">

# 适用于 Filament 的 Relation Navigator

<p class="filament-hidden">
    <a href="https://packagist.org/packages/wit3/filament-relation-navigator"><img src="https://img.shields.io/packagist/v/wit3/filament-relation-navigator.svg?style=flat-square" alt="Latest version on Packagist"></a>
    <a href="https://github.com/wit3/filament-relation-navigator/actions/workflows/tests.yml"><img src="https://img.shields.io/github/actions/workflow/status/wit3/filament-relation-navigator/tests.yml?branch=main&label=tests&style=flat-square" alt="Tests"></a>
    <a href="https://packagist.org/packages/wit3/filament-relation-navigator"><img src="https://img.shields.io/packagist/dt/wit3/filament-relation-navigator.svg?style=flat-square" alt="Total downloads"></a>
    <a href="LICENSE.md"><img src="https://img.shields.io/packagist/l/wit3/filament-relation-navigator.svg?style=flat-square" alt="License"></a>
</p>

> **文档：** 指南、配置参考、升级指南和搜索，请访问
> **[wit3.github.io/filament-relation-navigator/zh](https://wit3.github.io/filament-relation-navigator/zh/)**。
>
> **最新版本：** [更新内容](https://wit3.github.io/filament-relation-navigator/zh/reference/changelog) [![Latest version on Packagist](https://img.shields.io/packagist/v/wit3/filament-relation-navigator.svg?style=flat-square&label=version)](https://github.com/wit3/filament-relation-navigator/releases/latest)
>
> **其他语言：** 文档还有以下语言版本：
> [English](https://wit3.github.io/filament-relation-navigator/)（原版） · [Italiano](https://wit3.github.io/filament-relation-navigator/it/) · [Español](https://wit3.github.io/filament-relation-navigator/es/) · [Português](https://wit3.github.io/filament-relation-navigator/pt/) · [Français](https://wit3.github.io/filament-relation-navigator/fr/) · [Deutsch](https://wit3.github.io/filament-relation-navigator/de/) · [日本語](https://wit3.github.io/filament-relation-navigator/ja/) · [Русский](https://wit3.github.io/filament-relation-navigator/ru/) · [العربية](https://wit3.github.io/filament-relation-navigator/ar/)。每种语言在本仓库中也有自己的 README（`README.md`、`README.it.md`……）。

<!-- #region intro -->
为 Filament resource 的每个 relation manager 提供**独立的页面**，并通过**面包屑中的菜单**在它们之间切换。

- `/customers/1?relation=orders` 变成 **`/customers/1/orders`**：一个真正的路由，可以链接、加入书签，也可以在新标签页中打开。
- 页面渲染的就是**你已有的 relation manager**：同样的表格、筛选器、操作、徽章和权限控制。没有任何重复。
- 可以在关联上方添加一个**页头**（记录的卡片、widget、操作），内容取自 resource。
- 面包屑的最后一项会打开一个**菜单**，列出记录的各个页面以及用户能看到的所有关联，带有图标和徽章。两种外观，可选的搜索和键盘导航，可以使用你自己的视图，还有适配小屏幕的版本。
- 在同一页面上切换**上一条和下一条记录**，每个 relation manager 表格上都有一个**作为页面打开**操作，指向旧标签页的链接会跳转到新页面：每一项都可以全局、按 resource 或按页面开关。
- **一个 trait，无需页面类**：不用生成文件，也不用往 `getPages()` 或 panel provider 里添加任何东西。
- **32 种语言**（包括从右到左书写的语言），语言代码与 Filament 相同。文档提供 10 种语言版本。

<img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/demo.gif" alt="用 Alt Shift R 打开菜单，筛选出 Invoices，然后在同一页面上切换到下一个客户" width="880">
<!-- #endregion intro -->

## 环境要求

<!-- #region requirements -->
| 包       | 版本                 |
| -------- | -------------------- |
| PHP      | 8.2+                 |
| Laravel  | 12, 13               |
| Filament | 4.13+, 5.x           |
<!-- #endregion requirements -->

## 安装

<!-- #region installation -->
```bash
composer require wit3/filament-relation-navigator
php artisan filament:assets
```

每次更新后都要重新运行 `filament:assets`：Filament 会把插件的样式表复制到 `public/`。
<!-- #endregion installation -->

## 快速开始

<!-- #region quick-start -->
**1. 为关联提供页面。** 在 resource 中添加 trait：

```php
use Wit3\FilamentRelationNavigator\Concerns\HasRelationPages;

class CustomerResource extends Resource
{
    use HasRelationPages;

    public static function getRelations(): array
    {
        return [
            'orders' => OrdersRelationManager::class,
            'invoices' => InvoicesRelationManager::class,
            NotesRelationManager::class,
        ];
    }
}
```

现在每个关联都有了自己的页面：`/customers/1/orders`、`/customers/1/invoices`、`/customers/1/notes`。

**2. 在查看页面中添加菜单：**

```php
use Wit3\FilamentRelationNavigator\Concerns\HasRelationNavigator;

class ViewCustomer extends ViewRecord
{
    use HasRelationNavigator;
}
```

就这么简单。关联页面已经自带菜单，查看页面上的标签页也照常工作。

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-light.png" alt="一个客户的订单显示在独立的页面上，面包屑菜单已展开">
</picture>
<!-- #endregion quick-start -->

## 关联页面

<!-- #region relation-pages -->
### 启用

可以在 resource 上为所有 relation manager 启用，也可以只在需要的 relation manager 上启用：

```php
use Wit3\FilamentRelationNavigator\Concerns\InteractsWithRelationPage;

class OrdersRelationManager extends RelationManager
{
    use InteractsWithRelationPage;
}
```

| 设置方式                                     | 拥有页面的关联                      |
| ------------------------------------------- | ----------------------------------- |
| 在 resource 上使用 `HasRelationPages`        | 全部，除了主动排除的                |
| 在 manager 上使用 `InteractsWithRelationPage` | 只有启用了它的 manager              |
| 你自己编写的页面类（见下文）                  | 全部，除了主动排除的                |

要排除某些关联：

```php
// 在 resource 上
public static function hasRelationPage(string $slug): bool
{
    return $slug !== 'notes';
}

// 在 relation manager 上，配合 InteractsWithRelationPage
protected static bool $hasRelationPage = false;
```

被两个 resource 共用的 relation manager 会在两边都启用：用 resource 的 `hasRelationPage()` 排除其中一个。

### URL 和 slug

| `getRelations()` 条目                           | URL                         |
| ----------------------------------------------- | --------------------------- |
| `'orders' => OrdersRelationManager::class`      | `/customers/1/orders`       |
| `SupportTicketsRelationManager::class`（不带键） | `/customers/1/support-tickets` |
| `RelationGroup::make('Activity', [...])`        | `/customers/1/activity`     |

relation manager 自己设置的 slug 优先（`protected static ?string $relationPageSlug = 'tickets';`），其次是 `getRelations()` 中的键，最后是 kebab-case 形式的关联关系名称（或分组标签：请给分组设置键，因为翻译后的标签会随语言变化）。与 resource 某个页面键（`edit`、`view`……）相同的 slug 会被跳过，slug 已被占用的第二个关联也会被跳过。

每个页面都是一个命名路由 `relations.{slug}`：

```php
CustomerResource::getUrl('relations.orders', ['record' => $customer]);
```

### 授权与行为

relation manager 接收 resource 的**查看**页面作为 `$pageClass`（没有查看页面时为编辑页面），因此 `isReadOnly()`、`canViewForRecord()`、`getBadge()` 以及其他所有检查的行为都与在标签页中完全一致。查看页面通过键（`view`、`edit`，或你自己编写的页面类上的 `$contextPage`）或类来查找。

关联页面需要满足查看页面的要求（它的 `canAccess()` 以及记录的 `view` policy；当它代表编辑页面时为 `update`），以及 manager 的 `canViewForRecord()`。没有查看页面和编辑页面时，适用 `view` policy。以“未找到”方式拒绝的 policy 会返回 404，未知的 slug 也一样。菜单、全局搜索和“作为页面打开”只会链接用户能打开的页面。

关联页面不会运行查看页面自己的代码：它的 `authorizeAccess()` 中的检查，或按用户过滤的 `getRelationManagers()`，在这里都不生效。请把它们放到 policy 或 `canViewForRecord()` 中，或者在 resource 上按记录否决某个关联页面：

```php
public static function canAccessRelationPage(Model $record, string $slug): bool
{
    return $slug !== 'audit-logs' || auth()->user()->isAdmin();
}
```
<!-- #endregion relation-pages -->

## 页头、操作和 widget

<!-- #region header-actions-and-widgets -->
关联页面可以在关联上方显示记录查看页面的一部分、页头操作和 widget。在 resource 上定义它们会作用于所有关联页面，在 relation manager 上定义则只作用于它自己的页面：

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-light.png" alt="一个关联页面，订单上方有 widget、客户卡片和一个页头操作" width="880">
</picture>

```php
public static function relationPageHeader(Schema $schema): Schema
{
    // 整个 infolist（static::infolist($schema)）或其中一部分。
    return $schema->components([
        CustomerCard::make(),
    ]);
}

public static function getRelationPageHeaderActions(): array
{
    return [
        Action::make('email')
            ->url(fn (Customer $record): string => "mailto:{$record->email}"),
    ];
}

public static function getRelationPageHeaderWidgets(): array
{
    return [CustomerStats::class]; // 通过 $record 接收记录
}

public static function getRelationPageFooterWidgets(): array
{
    return [];
}
```

- relation manager 的页头会替换 resource 的页头；两者的操作和 widget 都会显示，resource 的在前。
- 页头操作作用于关联所属的记录。
- 有页头时，记录名称不会在标题下方重复显示。
- 只存在于查看页面类内部的东西（它自己的方法和模态框）在这里无法访问：请把它们移到 resource、action 类或 trait 中。
<!-- #endregion header-actions-and-widgets -->

## 面包屑菜单

<!-- #region the-breadcrumb-menu -->
`HasRelationNavigator` 需要一个带记录的页面：`ViewRecord`、`EditRecord` 以及使用 `InteractsWithRecord` 的页面已经具备它声明的两个方法 `getRecord()` 和 `hasRecord()`。在没有记录的页面上，PHP 会在类加载时立即报错。

记录页面当且仅当使用了 `HasRelationNavigator` 时才会出现在菜单中：查看页面、编辑页面或记录的任何自定义页面。在这些页面上，面包屑的最后一项会打开菜单。关联页面总会列出，排在记录页面之后，带有 relation manager 的图标和徽章，并且只在用户能看到时才显示。

如果页面已经自己构建面包屑，把它们传给 `withRelationNavigator()`：

```php
public function getBreadcrumbs(): array
{
    return $this->withRelationNavigator([
        CustomerResource::getUrl('index') => 'Customers',
        $this->getBreadcrumb(),
    ]);
}
```

自己使用 `InteractsWithRecord`、而不是继承 `ViewRecord` 或 `EditRecord` 的自定义页面，会有两个 `getBreadcrumbs()` 方法。除非它定义了自己的版本，否则需要告诉 PHP 使用哪一个：

```php
class CustomerActivity extends Page
{
    use HasRelationNavigator, InteractsWithRecord {
        HasRelationNavigator::getBreadcrumbs insteadof InteractsWithRecord;
        InteractsWithRecord::getBreadcrumbs as getRecordBreadcrumbs;
    }
}
```

菜单使用页面的 `$navigationLabel` 和 `$navigationIcon`，并且只在 `canAccess()` 对该记录允许时才显示该页面。
<!-- #endregion the-breadcrumb-menu -->

## 作为页面打开

<!-- #region open-as-page -->
在查看页面和编辑页面上，每个拥有独立页面的 relation manager 都会在其表格的页头中获得一个**作为页面打开**操作，位于标题旁边、它自己的操作之前。它会打开该 relation manager 自己的页面；如果它没有自己的页面，则打开其关联分组的页面。在分组的页面上，拥有独立页面的 manager 同样会有这个操作；在自己的页面上，manager 永远不会链接到自身。

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/open-as-page-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/open-as-page-light.png" alt="查看页面上，Orders 表格页头中的“作为页面打开”操作" width="880">
</picture>

在需要的地方关闭它：

```php
// config/filament-relation-navigator.php：所有地方
'relation_manager_link' => false,
```

```php
// 在 resource 上：它的所有 relation manager
public static function hasRelationPageLinks(): bool
{
    return false;
}
```

```php
// 在 relation manager 上（配合 InteractsWithRelationPage）
protected static bool $hasRelationPageLink = false;

// 或者，不使用该 trait
public static function hasRelationPageLink(): bool
{
    return false;
}
```

### 外观

默认是一个带箭头的链接。可以在配置中修改：

```php
'relation_manager_action' => [
    'style' => 'button',        // link、button 或 icon_button（标签会变成 tooltip）
    'label' => '完整页面',      // 文本或翻译键；null：“作为页面打开”，已翻译
    'icon' => 'heroicon-m-arrow-top-right-on-square', // 任意 Filament 图标，或 null
    'icon_position' => 'after', // 或 before
    'color' => 'gray',
    'size' => null,             // sm、md、lg……
    'new_tab' => false,
],
```

也可以直接修改 Filament 操作本身：除了 URL 和显示时机由本包决定，其他都可以改。每一层拿到的操作都是上一层处理后的结果：

```php
// 在服务提供者中：所有 relation manager
RelationNavigator::configureRelationPageActionUsing(
    fn (Action $action, RelationManager $relationManager) => $action->outlined()->badge('新'),
);
```

```php
// 在 resource 或 relation manager 上：只作用于那里
public static function configureRelationPageAction(Action $action): Action
{
    return $action->iconButton()->tooltip('在独立页面中打开订单');
}
```

`RelationNavigator::getRelationManagerPageUrl($relationManager)` 返回同样的 URL，可用于你自己的按钮。
<!-- #endregion open-as-page -->

## 上一条和下一条记录

<!-- #region previous-and-next-record -->
页头操作前面的两个按钮会**在同一页面上**打开上一条或下一条记录：从一个客户的发票跳到下一个客户的发票。

<img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/record-navigation.gif" alt="上一条和下一条记录：从一个客户的发票跳到下一个客户的发票，tooltip 中显示记录名称" width="880">

无法打开该页面的记录，会打开它在菜单中的第一个页面。这两个按钮默认关闭，因为每次渲染都会多出几次查询：

```php
'record_navigation' => true,
```

它们按照**你进入时所在的列表**切换：与屏幕上一致的排序、筛选、搜索和标签页（最多 100 条记录；使用 UUID 键时约 30 条）。到了该列表的第一条或最后一条记录就不再继续，而不是跳到另一种顺序。不在列表中的记录（从链接打开）按键的顺序切换。要始终按键的顺序切换：

```php
'record_navigation_follows_list' => false,
```

只有在配置或 resource（`hasRelationNavigatorRecordNavigation()`）中开启了记录导航时，列表才会记住它的记录，因为列表页面无法询问记录页面。

它们使用 resource 自己的查询（`getRecordRouteBindingEloquentQuery()`），因此多租户和你的 scope 都会生效，并且会跳过用户无权查看的记录。按页面设置：

```php
public function hasRelationNavigatorRecordNavigation(): bool
{
    return true;
}

public function getRelationNavigatorRecordNavigationQuery(): ?Builder
{
    return Customer::query()->where('active', true);
}
```

在**嵌套 resource** 上，它们只在父记录内切换：从一个客户的某个订单到同一客户的下一个订单。此时的列表是父级的 relation manager：当嵌套 resource 是它的关联 resource（`protected static ?string $relatedResource`）时，按它的顺序切换；使用 `cookie` session 驱动时除外，此时只保留父级的列表。
<!-- #endregion previous-and-next-record -->

## 指向 relation manager 标签页的链接

<!-- #region links-to-relation-manager-tabs -->
关联页面取代标签页之后，像 `/customers/1?relation=orders` 这样的旧链接可以改为打开 `/customers/1/orders`：

```php
'redirect_relation_tabs' => true,
```

没有关联页面的标签页，或记录无法看到的标签页，保持原样。
<!-- #endregion links-to-relation-manager-tabs -->

## 全局搜索

<!-- #region global-search -->
全局搜索结果可以直接链接到记录的关联页面：搜索一个客户，直接跳到它的订单。默认关闭，因为它会对每个结果运行 relation manager 的 `canViewForRecord()`：

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/global-search-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/global-search-light.png" alt="全局搜索结果中的一个客户，带有指向其订单、发票、地址和联系人的链接" width="420">
</picture>

```php
// config/filament-relation-navigator.php
'global_search_actions' => 3, // true 表示全部，或每个结果最多显示的链接数
```

```php
// 在 resource 上：只对此 resource 开启或关闭
public static function hasRelationPageGlobalSearchActions(): bool
{
    return true;
}
```

```php
// 在 relation manager 上（配合 InteractsWithRelationPage）：永不链接
protected static bool $hasRelationPageGlobalSearchAction = false;
```

使用 `HasRelationPages` 的 resource 会自动获得这些链接。自己定义了 `getGlobalSearchResultActions()` 的 resource，或只通过 relation manager 启用的 resource，需要这样把它们合并进去：

```php
public static function getGlobalSearchResultActions(Model $record): array
{
    return [
        // ...你的操作
        ...RelationNavigator::getGlobalSearchResultActions(static::class, $record),
    ];
}
```

这些链接遵循与菜单相同的规则：只包含用户能打开的关联页面。
<!-- #endregion global-search -->

## 菜单预设

<!-- #region menu-presets -->
发布配置文件：

```bash
php artisan vendor:publish --tag=filament-relation-navigator-config
```

```php
'menu' => [
    'preset' => 'github',   // 'simple'、你自己的预设或视图名称
    'presets' => [
        'simple' => 'filament-relation-navigator::menu.simple',
        'github' => 'filament-relation-navigator::menu.github',
    ],
    'searchable' => true,   // 在列表顶部显示搜索框，与预设无关
    'search_from' => 0,     // ……从这么多项开始
    'badges' => 'lazy',     // relation manager 的徽章：'lazy' 在菜单打开时加载，true 随页面加载，false 关闭
    'max_height' => '24rem',
    'separator' => true,    // 记录页面和关联页面分成两组
    'mobile' => true,       // 在小屏幕上，菜单显示在标题上方
    'shortcut' => 'alt+shift+r', // 打开菜单；null 表示关闭
],
```

**github**（默认）：面包屑的最后一项保持为文本，旁边的小箭头会打开一个面板，其中有标题、搜索框，当前页面上有一个对勾，类似 GitHub 的仓库切换器。菜单打开时搜索框自动获得焦点，按 Enter 打开第一个匹配项。

**simple**：面包屑的最后一项本身就会打开列表。要使用 1.2 及更早版本的菜单，设置 `'preset' => 'simple'` 和 `'searchable' => false`。

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/menu-simple-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/menu-simple-light.png" alt="查看页面上的 simple 预设" width="520">
</picture>

### 徽章

菜单会显示 relation manager 的徽章，与它们的标签页一样。默认情况下，徽章**在菜单打开时加载**：在此之前菜单是关闭的，所以页面渲染时不会执行它们的查询，每次重绘面包屑的 Livewire 请求也是如此。加载的那一小会儿会显示一个加载动画。

```php
'badges' => 'lazy', // 默认；true：随页面加载；false：不显示徽章
```

在需要的地方关闭它们：

```php
// 在页面或 resource（static）上：此菜单中不显示徽章
public function hasRelationNavigatorBadges(): bool
{
    return false;
}

// 在 relation manager 上（配合 InteractsWithRelationPage）：它的徽章保留在
// 标签页中，不显示在菜单里
protected static bool $hasRelationNavigatorBadge = false;
```

在 Filament 中标签页徽章为 lazy 的 relation manager（`protected static ?bool $isBadgeLazyLoaded = true;`，或 Filament 4.13 上的 `$isBadgeDeferred`），在菜单中也是 lazy 的，即使设置了 `'badges' => true`。

无法延迟加载徽章的菜单视图（1.3 之前发布的本包视图，或自己渲染 `$item->badge` 的自定义预设）会像以前一样随页面获得徽章：不会有任何东西消失。

**键盘**：在搜索框中按向下键进入列表；向上和向下键在页面之间移动，Enter 打开页面，Escape 关闭菜单。

### 快捷键和 tooltip

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/tooltip-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/tooltip-light.png" alt="菜单按钮的 tooltip，显示其快捷键" width="470">
</picture>

打开菜单的按钮带有一个显示快捷键的 tooltip，按 Mac 或 Windows/Linux 的按键显示。快捷键可以在页面的任何位置打开菜单，并且搜索框已准备好输入，类似 GitHub 的仓库切换器：Mac 上是 <kbd>⌥</kbd> <kbd>⇧</kbd> <kbd>R</kbd>，其他系统上是 <kbd>Alt</kbd> <kbd>Shift</kbd> <kbd>R</kbd>。可以在配置中或按页面修改：

```php
'shortcut' => 'mod+shift+p', // mod 在 Mac 上是 ⌘，在其他系统上是 Ctrl
```

```php
public function getRelationNavigatorShortcut(): ?string
{
    return null; // 此页面不使用快捷键
}
```

它的设计尽量不打扰你：

- 它匹配的是物理按键，因此适用于任何键盘布局，也适用于 Mac 上的 Option 键（在 Mac 上 <kbd>⌥</kbd><kbd>⇧</kbd><kbd>R</kbd> 会输入 ‰）；
- 在输入框中打字时它不会触发；
- 绝不会占用 panel 全局搜索的快捷键（`globalSearchKeyBindings()`）：这种情况下菜单没有快捷键；
- 只会打开可见的那个菜单（面包屑中的或小屏幕上的）。

支持的按键：`alt`、`shift`、`ctrl`、`meta`、`mod` 加上一个字母、一个数字或 `/ . , ;`。

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/menu-github-search-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/menu-github-search-light.png" alt="带搜索框的 github 预设" width="700">
</picture>

### 页面、resource 还是配置

每个选项都有三个层级。页面优先，其次是 resource，最后是配置：

| 页面方法（在 resource 上为 static） | 配置键 |
|---|---|
| `getRelationNavigatorPreset(): string` | `menu.preset` |
| `isRelationNavigatorSearchable(): bool` | `menu.searchable` |
| `hasRelationNavigatorBadges(): bool` | `menu.badges` |
| `hasDeferredRelationNavigatorBadges(): bool` | `menu.badges` = `'lazy'` |
| `getRelationNavigatorShortcut(): ?string` | `menu.shortcut` |
| `hasRelationNavigatorMobileMenu(): bool` | `menu.mobile` |
| `hasRelationNavigatorRecordNavigation(): bool` | `record_navigation` |
| `shouldRelationNavigatorRecordNavigationFollowList(): bool` | `record_navigation_follows_list` |
| `shouldRedirectRelationNavigatorTabs(): bool` | `redirect_relation_tabs` |

```php
// 在某个页面上
public function isRelationNavigatorSearchable(): bool
{
    return false;
}
```

```php
// 在 resource 上：同一个方法，static，作用于它的所有页面
public static function hasRelationNavigatorRecordNavigation(): bool
{
    return true;
}
```

### 小屏幕

Filament 在 40rem 以下会隐藏面包屑。此时菜单会显示在页面标题上方，适用于所有使用 `HasRelationNavigator` 的页面，预设选项相同。用 `'mobile' => false` 关闭，或按页面关闭：

```php
public function hasRelationNavigatorMobileMenu(): bool
{
    return false;
}
```

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/mobile-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/mobile-light.png" alt="手机上的菜单，位于页面标题上方" width="320">
</picture>

### 翻译

支持 32 种语言，语言代码与 Filament 相同：阿拉伯语、保加利亚语、加泰罗尼亚语、中文（简体和繁体）、捷克语、丹麦语、荷兰语、英语、芬兰语、法语、德语、希腊语、希伯来语、匈牙利语、印度尼西亚语、意大利语、日语、韩语、挪威语、波斯语、波兰语、葡萄牙语（葡萄牙和巴西）、罗马尼亚语、俄语、斯洛伐克语、西班牙语、瑞典语、土耳其语、乌克兰语和越南语。在从右到左书写的语言中，上一条和下一条的箭头会反过来。发布翻译文件即可修改翻译或添加新语言：

```bash
php artisan vendor:publish --tag=filament-relation-navigator-translations
```

### 自定义预设

从本包的某个预设开始：

```bash
php artisan make:filament-relation-navigator-preset compact --from=github
```

它会把预设复制到 `resources/views/filament/relation-navigator/compact.blade.php`。注册它，然后随意修改：

```php
'preset' => 'compact',
'presets' => [
    // ...
    'compact' => 'filament.relation-navigator.compact',
],
```

任何视图名称也都可以用作 `preset`。要修改本包自带的预设，发布它们的视图：

```bash
php artisan vendor:publish --tag=filament-relation-navigator-views
```

预设视图会接收以下变量：

| 变量                 | 内容                                                                    |
| -------------------- | ----------------------------------------------------------------------- |
| `$label`             | 面包屑的最后一项                                                        |
| `$items`             | 菜单项：`key`、`label`、`url`、`icon`、`badge`、`badgeColor`、`isActive` |
| `$heading`           | "Switch page"，已翻译                                                   |
| `$isSearchable`      | 是否显示搜索框                                                          |
| `$hasSeparator`      | 是否把记录页面和关联页面分成两组                                        |
| `$shortcut`          | 快捷键，或 null                                                         |
| `$shortcutKeys`      | 要显示的快捷键按键：`['mac' => [...], 'other' => [...]]`                |
| `$searchPlaceholder` | "Find a page…"，已翻译                                                  |
| `$emptyLabel`        | "No pages found."，已翻译                                               |
| `$maxHeight`         | `menu.max_height`                                                       |

要让你的按钮带上 tooltip 和快捷键，在它的标签内引入 `filament-relation-navigator::menu.partials.trigger-attributes`。每个菜单项还有 `isRelationPage()`。要复用搜索框、列表（包括分隔线和键盘导航）以及“无结果”那一行，引入 `filament-relation-navigator::menu.partials.list` 并传入 `showCheck`。
<!-- #endregion menu-presets -->

## 自定义关联页面

<!-- #region customising-the-relation-pages -->
### 单个关联的标题、副标题和面包屑

relation manager 可以根据记录为自己的页面设置这些内容，无需页面类：

```php
class OrdersRelationManager extends RelationManager
{
    public static function getRelationPageTitle(Model $ownerRecord): string|Htmlable|null
    {
        return "{$ownerRecord->name} 的订单";
    }

    public static function getRelationPageSubheading(Model $ownerRecord): string|Htmlable|null
    {
        return '最近 12 个月';
    }

    public static function getRelationPageBreadcrumb(Model $ownerRecord): ?string
    {
        return '订单';
    }
}
```

返回 `null` 则保留默认值。你自己编写的页面类（见下文）中的 `$title`、`$subheading` 和 `$breadcrumb` 优先。菜单和标签页仍使用 relation manager 的标题。关联分组没有 relation manager，所以它们的页面使用分组标签。

### 自己编写页面类

自动生成的关联页面能满足大多数需求。如果要修改它们的标题、面包屑，或者做 Filament 页面能做的其他任何事情，可以自己编写页面类；本包会对该 resource 使用你的类：

```bash
php artisan make:filament-relation-navigator CustomerResource
```

```php
class CustomerRelationPage extends RelationPage
{
    protected static string $resource = CustomerResource::class;

    protected static ?array $relations = ['orders', 'invoices'];  // 只包括这些
    protected static array $excludedRelations = ['notes'];         // 永远不包括这些
}
```

```php
public static function getPages(): array
{
    return [
        'index' => ListCustomers::route('/'),
        'view' => ViewCustomer::route('/{record}'),
        ...CustomerRelationPage::relationRoutes(),
    ];
}
```

这个类就是一个普通的 Filament 页面：可以重写 `getTitle()`、`getBreadcrumbs()`、`getHeaderActions()`，使用你自己的 trait，等等。
<!-- #endregion customising-the-relation-pages -->

## 原生记录子导航

<!-- #region native-record-sub-navigation -->
同样的菜单项也可以用于 Filament 的记录子导航：

```php
public static function getRecordSubNavigation(Page $page): array
{
    return RelationNavigator::getRecordSubNavigation($page);
}
```
<!-- #endregion native-record-sub-navigation -->

## 工作原理

<!-- #region how-it-works -->
- 路由通过 Filament 自己的 `Resource::registerRoutes()` 注册，位于每个 resource 的路由组内：slug、cluster、父级 resource 和租户都由 Filament 处理。
- resource 的页面类会按需写入 `storage/framework/cache`，方式与 Laravel 的实时 facade 相同，并注册到 Livewire。它兼容 `route:cache`。
- 关联页面像 Filament 在标签页中那样嵌入 relation manager，因此其状态、操作和模态框都保留在 relation manager 中。
<!-- #endregion how-it-works -->

## AI 智能体

<!-- #region ai-agents -->
本包会教 AI 编程智能体如何使用它，因此 Claude Code、Codex、Cursor、Copilot 等工具能正确地编写 trait 和选项：

- **Laravel Boost**：本包附带了指南和一个 skill（`relation-navigator-development`）。运行 `php artisan boost:install`（或 `boost:update`）时选择 `wit3/filament-relation-navigator`。
- **llms.txt**：文档也以面向智能体的形式发布，包括索引（[llms.txt](https://wit3.github.io/filament-relation-navigator/llms.txt)）和单个文件（[llms-full.txt](https://wit3.github.io/filament-relation-navigator/llms-full.txt)），均为英文。每个页面也都有 Markdown 版本：在其 URL 后加上 `.md` 即可。
- **不使用 Boost**：让你的智能体读取 `vendor/wit3/filament-relation-navigator/resources/boost/skills/relation-navigator-development/SKILL.md`，例如在你的 `AGENTS.md` 或 `CLAUDE.md` 中引用它。
<!-- #endregion ai-agents -->

## 测试

<!-- #region testing -->
```bash
composer test        # 测试套件
composer analyse     # PHPStan，级别 8
composer format      # Pint
```

测试套件在 Filament 4 和 5、Laravel 12 和 13、PHP 8.2 到 8.5 上运行，覆盖嵌套 resource、cluster、多租户和 SPA 模式。

浏览器测试（带搜索的菜单）需要 Playwright：

```bash
npm install && npx playwright install chromium
vendor/bin/testbench filament:assets
composer test:browser
```
<!-- #endregion testing -->

## 更新日志

<!-- #region changelog -->
参见 [CHANGELOG](https://github.com/wit3/filament-relation-navigator/blob/main/CHANGELOG.md)（英文）。
<!-- #endregion changelog -->

## 贡献

<!-- #region contributing -->
欢迎提交 issue 和 pull request。提交 pull request 之前，请运行 `composer test`、`composer analyse` 和 `composer format`。
<!-- #endregion contributing -->

## 安全

<!-- #region security -->
请通过 [GitHub security advisories](https://github.com/wit3/filament-relation-navigator/security/advisories/new) 私下报告安全问题，不要在公开的 issue 中报告。
<!-- #endregion security -->

## 致谢

<!-- #region credits -->
- [Salvatore Scalzi](https://github.com/wit3)
- [所有贡献者](https://github.com/wit3/filament-relation-navigator/contributors)
<!-- #endregion credits -->

## 许可证

<!-- #region license -->
MIT 许可证（MIT）。参见 [LICENSE](https://github.com/wit3/filament-relation-navigator/blob/main/LICENSE.md)。
<!-- #endregion license -->
