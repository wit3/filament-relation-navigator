# 配置

发布配置文件，即可修改所有 resource 的默认值：

```bash
php artisan vendor:publish --tag=filament-relation-navigator-config
```

已发布的配置文件在各个 minor 版本之间都能继续使用：每个键在代码中都有默认值，因此只有当你想修改新键时才需要添加它们。

## 选项

大多数选项也可以**按 resource**（resource 上的静态方法）或**按页面或 relation manager** 设置：

- **菜单选项**（有对应方法的 `menu.*`、记录导航、标签页重定向）：页面优先，其次是 resource，最后是配置。
- **“作为页面打开”** 和 **relation manager 在菜单中的徽章**：只有每一层都允许时才会显示；任何一层都可以关闭它们。
- **全局搜索链接**：resource 的方法会替代该 resource 的配置；relation manager 只能把自己排除在外。
- **为上一条和下一条记住的列表**：只由 resource 或配置决定，因为列表页面无法询问记录页面。
- `menu.presets`、`menu.search_from`、`menu.max_height` 和 `menu.separator` 只能在配置中设置。

| 键 | 默认值 | 作用 | 其他层级 |
|---|---|---|---|
| `menu.preset` | `'github'` | 菜单的外观：`github`、`simple`、你自己的预设或视图名称 | `getRelationNavigatorPreset()` |
| `menu.presets` | `simple`、`github` | 预设的名称和视图 | |
| `menu.searchable` | `true` | 在列表顶部显示搜索框 | `isRelationNavigatorSearchable()` |
| `menu.search_from` | `0` | 菜单项达到这个数量时才显示搜索框 | |
| `menu.badges` | `'lazy'` | `'lazy'`：徽章在菜单打开时加载；`true`：随页面加载；`false`：不显示 | `hasRelationNavigatorBadges()`、`hasDeferredRelationNavigatorBadges()`；relation manager：`hasRelationNavigatorBadge()` |
| `menu.max_height` | `'24rem'` | 列表开始滚动前的高度（CSS 长度） | |
| `menu.separator` | `true` | 记录页面和关联页面分成两组 | |
| `menu.mobile` | `true` | 在小屏幕上，菜单显示在标题上方 | `hasRelationNavigatorMobileMenu()` |
| `menu.shortcut` | `'alt+shift+r'` | 打开菜单的键盘快捷键；`null`：不使用 | `getRelationNavigatorShortcut()` |
| `relation_manager_link` | `true` | relation manager 表格上的“作为页面打开”操作 | resource `hasRelationPageLinks()`，relation manager `hasRelationPageLink()` |
| `relation_manager_action` | 带箭头的链接 | 该操作的外观：`style`、`label`、`icon`、`icon_position`、`color`、`size`、`new_tab` | `configureRelationPageActionUsing()`、`configureRelationPageAction()` |
| `global_search_actions` | `false` | 全局搜索结果下指向关联页面的链接：`true` 或每个结果的最大数量 | resource `hasRelationPageGlobalSearchActions()`，relation manager `hasRelationPageGlobalSearchAction()` |
| `record_navigation` | `false` | 上一条和下一条记录按钮 | `hasRelationNavigatorRecordNavigation()` |
| `record_navigation_follows_list` | `true` | 上一条和下一条按照用户进入时所在的列表切换 | `shouldRelationNavigatorRecordNavigationFollowList()` |
| `redirect_relation_tabs` | `false` | 查看页面上的 `?relation=x` 会打开关联页面 | `shouldRedirectRelationNavigatorTabs()` |

页面方法是使用 `HasRelationNavigator` 的记录页面上的实例方法；在 resource 上，同一个方法是静态的，作用于它的所有页面。参见[页面、resource 还是配置](/zh/guide/menu-presets#页面、resource-还是配置)。

## 完整文件

当前版本附带的配置文件（注释为英文）：

<<< ../../../config/filament-relation-navigator.php
