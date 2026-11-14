# Рецепты

Короткие ответы для частых случаев. Каждый использует только публичный API.

## Страницы только для некоторых связей

Не трогайте ресурс и включите страницы в тех relation manager, где они нужны:

```php
use Wit3\FilamentRelationNavigator\Concerns\InteractsWithRelationPage;

class OrdersRelationManager extends RelationManager
{
    use InteractsWithRelationPage;
}
```

## Страницы вместо вкладок

Оставьте связи на отдельных страницах и уберите вкладки со страницы просмотра. Старые ссылки на вкладки (`?relation=orders`) ведут на страницы:

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

Меню, «Открыть как страницу» и глобальный поиск продолжают показывать страницы связей: они читают `getRelations()` ресурса.

## Данные записи над каждой связью

Используйте infolist ресурса или его часть:

```php
// CustomerResource
public static function relationPageHeader(Schema $schema): Schema
{
    return static::infolist($schema);
}
```

## Ресурс без страницы просмотра

Страницы связей работают и только со страницей списка. Дайте на них ссылку из таблицы:

```php
Action::make('orders')
    ->url(fn (Customer $record): string => CustomerResource::getUrl('relations.orders', ['record' => $record]))
```

Им нужна политика `view` для записи, потому что нет страницы просмотра, которую они бы заменяли.

## Страница просмотра под другим ключом

`'details' => ViewCustomer::route('/{record}')` находится по своему классу. В своём классе страницы связи её можно указать и явно: `protected static ?string $contextPage = 'details';`.

## Ссылка на страницу связи откуда угодно

```php
CustomerResource::getUrl('relations.orders', ['record' => $customer]);
```

Внутри тенанта, кластера или вложенного ресурса Filament подставляет недостающие параметры из текущего запроса, как для любого URL ресурса.

## Простое меню

Меню версии 1.2: хлебные крошки открывают простой список, без поиска, бейджей и сочетания клавиш.

```php
'menu' => [
    'preset' => 'simple',
    'searchable' => false,
    'badges' => false,
    'shortcut' => null,
],
```

## Только для сотрудников

Скройте страницу связи для конкретной записи или для некоторых пользователей на ресурсе:

```php
public static function canAccessRelationPage(Model $record, string $slug): bool
{
    return $slug !== 'audit-logs' || auth()->user()->isStaff();
}
```

## Несколько панелей или тенантов

Ничего делать не нужно: маршруты регистрируются для каждой панели, в которой есть ресурс, внутри её тенанта и кластера, а «предыдущая» и «следующая» запоминают по одному списку на панель, тенант и ресурс.
