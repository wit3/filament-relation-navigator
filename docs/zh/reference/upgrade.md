# 升级指南

本包遵循[语义化版本](https://semver.org/lang/zh-CN/)：minor 和 patch 版本永远不会破坏你的代码。本页列出更新时你可能需要关注的内容。[更新日志](/zh/reference/changelog)中有全部变更。

```bash
composer update wit3/filament-relation-navigator
php artisan filament:assets
```

## 升级到 1.7

无需修改。开启记录导航后，**嵌套 resource 也会显示上一条和下一条**，范围限定在同一个父记录内；当嵌套 resource 是父级 relation manager 的关联 resource（`$relatedResource`）时，按该 relation manager 的顺序切换。要在某个嵌套 resource 上隐藏它们，在其页面或 resource 上让 `hasRelationNavigatorRecordNavigation()` 返回 `false`。

## 升级到 1.6

无需修改。

- 从 1.6.1 起，本包的内部实现被标记为 `@internal`（参见更新日志）：如果你调用了 `RelationNavigator::optsIn()`、`registerRoutes()` 或类似的辅助方法，请改用 [API 参考](/zh/reference/api)中列出的内容。
- 从 1.6.1 起，允许的最低 Filament 版本是 4.13。

- **菜单徽章在菜单打开时加载**，而不是随页面加载。要恢复以前的行为：`'badges' => true`。1.3 之前发布的菜单视图，以及你自己的预设，仍会随页面获得徽章。
- “作为页面打开”操作有了新的选项（`relation_manager_action`）和钩子：参见[作为页面打开](/zh/guide/open-as-page)。

## 升级到 1.5

无需修改。有两处行为不同：

- 开启记录导航后，**上一条和下一条按照你进入时所在的列表切换**（排序、筛选、搜索、标签页），而不是按键的顺序。要保留按键的顺序：`'record_navigation_follows_list' => false`。
- **“作为页面打开”** 会优先打开 relation manager 自己的页面，而不是它所属的关联分组的页面。

如果你发布了本包的视图，请重新发布 `menu/partials/list.blade.php`：标签页徽章为 lazy 的 relation manager 现在在菜单中也会延迟加载徽章。

## 升级到 1.4

无需修改。

- **“作为页面打开”** 链接现在是 relation manager 表格页头中的一个操作，而不是表格上方的链接。如果你想在某些地方关闭它，参见[作为页面打开](/zh/guide/open-as-page)：配置、resource 或 relation manager。
- `RelationNavigator::renderRelationManagerLink()` 已弃用：本包不再使用它。它仍可用于你自己的 render hook，将在 2.0 中移除。
- 现在每个菜单选项也都可以在 resource 上设置：参见[页面、resource 还是配置](/zh/guide/menu-presets#页面、resource-还是配置)。

## 升级到 1.3

::: danger 安全修复
1.0.0 到 1.2.1 版本没有在关联页面上应用记录的 `view` policy（[GHSA-v3mh-w7qp-37gc](https://github.com/wit3/filament-relation-navigator/security/advisories/GHSA-v3mh-w7qp-37gc)）。如果你的记录使用了 policy，请更新。
:::

- **默认菜单现在是带搜索框的 `github` 预设。** 如果你发布了配置文件，会保留你的设置。要保留 1.2 的菜单：

  ```php
  // config/filament-relation-navigator.php
  'menu' => [
      'preset' => 'simple',
      'searchable' => false,
  ],
  ```

- 菜单不再向无权编辑记录的用户列出编辑页面。
- 新增的选项在代码中都有默认值：从 1.2 发布的配置文件可以继续使用。想修改时再把它们添加到配置中：`relation_manager_link`、`record_navigation`、`redirect_relation_tabs`，以及 `menu.badges` 的 `'lazy'`。
- 如果你发布了本包的视图并想使用 `'badges' => 'lazy'`，请重新发布 `menu/partials/list.blade.php`。

## 升级到 1.2

- `HasRelationNavigator` 现在声明了 `getRecord()` 和 `hasRecord()`：没有记录的页面会在类加载时立即失败，而不是之后才失败。查看页面、编辑页面和使用 `InteractsWithRecord` 的页面已经具备这两个方法。
