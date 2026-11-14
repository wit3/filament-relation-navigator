# 常见用法

常见设置的简短解答。每一个都只使用公开 API。

## 只为部分关联提供页面

不用改动 resource，只在需要的 relation manager 上启用：

```php
use Wit3\FilamentRelationNavigator\Concerns\InteractsWithRelationPage;

class OrdersRelationManager extends RelationManager
{
    use InteractsWithRelationPage;
}
```

## 用页面代替标签页

把关联放在各自的页面上，并从查看页面中去掉标签页。指向标签页的旧链接（`?relation=orders`）会跳转到这些页面：

```php
// ViewCustomer
public function getRelationManagers(): array
{
    return [];
}
```

```php
// config/filament-relation-navigator.php
'redirect_relation_tabs' => true,
```

菜单、“作为页面打开”和全局搜索仍会列出关联页面：它们读取的是 resource 的 `getRelations()`。

## 在每个关联上方显示记录详情

复用 resource 的 infolist，或其中的一部分：

```php
// CustomerResource
public static function relationPageHeader(Schema $schema): Schema
{
    return static::infolist($schema);
}
```

## 没有查看页面的 resource

只有列表页面时，关联页面也能工作。从表格中链接它们：

```php
Action::make('orders')
    ->url(fn (Customer $record): string => CustomerResource::getUrl('relations.orders', ['record' => $record]))
```

它们需要记录的 `view` policy，因为没有可以代表的查看页面。

## 使用其他键的查看页面

`'details' => ViewCustomer::route('/{record}')` 会通过它的类被找到。如果你自己编写了关联页面类，也可以指定它：`protected static ?string $contextPage = 'details';`。

## 从任何地方链接到关联页面

```php
CustomerResource::getUrl('relations.orders', ['record' => $customer]);
```

在租户、cluster 或嵌套 resource 中，Filament 会从当前请求中补全缺少的参数，与任何 resource URL 一样。

## 简洁的菜单

1.2 的菜单：面包屑打开一个普通列表，没有搜索、徽章和快捷键。

```php
'menu' => [
    'preset' => 'simple',
    'searchable' => false,
    'badges' => false,
    'shortcut' => null,
],
```

## 仅限员工

在 resource 上按记录或为部分用户隐藏某个关联页面：

```php
public static function canAccessRelationPage(Model $record, string $slug): bool
{
    return $slug !== 'audit-logs' || auth()->user()->isStaff();
}
```

## 多个 panel 或租户

无需任何操作：路由会为每个包含该 resource 的 panel 注册，位于其租户和 cluster 内；上一条/下一条会为每个 panel、租户和 resource 分别记住一个列表。
