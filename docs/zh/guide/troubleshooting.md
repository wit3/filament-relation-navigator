# 故障排除

## 菜单没有显示

- 页面必须使用 `HasRelationNavigator`。关联页面总是带有它。
- 菜单至少需要两项：页面本身和另外一项。
- 自己构建面包屑的页面必须返回 `$this->withRelationNavigator([...])`。
- 在 40rem 以下 Filament 会隐藏面包屑：菜单会移到标题上方（`menu.mobile`）。

## 菜单显示异常或小箭头位置不对

每次更新后，重新发布插件的样式表：

```bash
php artisan filament:assets
```

## 关联页面返回 404

- 该 relation manager 没有页面：resource 需要使用 `HasRelationPages`，或者 relation manager 需要使用 `InteractsWithRelationPage`，并且它没有被排除。
- 它的 slug 与 resource 的某个页面键（`edit`、`view`……）相同：这样的 slug 会被跳过。用 `$relationPageSlug` 换一个。
- 如果缓存了路由，部署后要重新缓存：`php artisan route:cache`。
- policy 以“未找到”方式拒绝了该记录（`Response::denyAsNotFound()`）：这个 404 是有意为之。

## 关联页面返回 403

关联页面需要查看页面的 `canAccess()`、记录的 `view` policy（编辑页面为 `update`）、relation manager 的 `canViewForRecord()`，以及 resource 的 `canAccessRelationPage()`（如果你定义了的话）。请按这个顺序检查。

## 徽章没有显示

- `menu.badges` 为 `false`，或者页面、resource 关闭了徽章，或者 relation manager 关闭了徽章（`hasRelationNavigatorBadge()`）。
- 默认情况下，徽章在菜单打开时加载，并显示一个加载动画。如果加载动画一直不停，请在浏览器控制台中查看是否有失败的 Livewire 请求。
- 1.3 之前发布的视图无法延迟加载徽章：它们会随页面获得徽章。

## 快捷键没有反应

- 在输入框中打字时它不会触发。
- panel 全局搜索的快捷键（`globalSearchKeyBindings()`）绝不会被占用：用 `menu.shortcut` 换一个。
- 浏览器扩展或操作系统可能占用了相同的按键。

## 缺少“作为页面打开”

- `relation_manager_link` 为 `false`，或者 resource 或 relation manager 关闭了它。
- 该 relation manager 没有自己的页面，或者你已经在它的页面上。
- 使用自定义 `->header()` 视图的表格没有放置页头操作的位置。

## 缺少上一条和下一条

- 它们默认是关闭的：`record_navigation`。
- 没有其他记录时它们会隐藏（在嵌套 resource 上，即同一父记录下没有其他记录）。
- 要按列表的顺序切换，必须在本次会话中打开过该列表，并且在配置或 resource 中开启了记录导航。

## “A directory that only this user can write”

storage 是只读的，因此生成的页面类会写入系统临时目录，而该目录属于其他用户。请让 `storage/framework/cache` 可写，删除消息中提到的目录，或者在服务提供者中指定一个目录：

```php
GeneratedRelationPages::useCachePath(storage_path('app/relation-pages'));
```

## 仍然无法解决

在 [GitHub Discussions](https://github.com/wit3/filament-relation-navigator/discussions) 中提问，或者提交一个 [issue](https://github.com/wit3/filament-relation-navigator/issues)，附上你的 Filament 版本和几行能复现问题的代码。
