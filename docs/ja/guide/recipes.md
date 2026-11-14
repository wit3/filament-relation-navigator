# レシピ

よくある構成への短い答えです。どれも公開 API だけを使っています。

## 一部のリレーションだけにページを用意する

リソースには手を加えず、必要なリレーションマネージャーで有効にします。

```php
use Wit3\FilamentRelationNavigator\Concerns\InteractsWithRelationPage;

class OrdersRelationManager extends RelationManager
{
    use InteractsWithRelationPage;
}
```

## タブの代わりにページを使う

リレーションは専用ページに置き、詳細ページからタブを外します。タブへの古いリンク（`?relation=orders`）はページへ転送されます。

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

メニュー、「ページとして開く」、グローバル検索は引き続きリレーションページを表示します。これらはリソースの `getRelations()` を参照しているためです。

## すべてのリレーションの上にレコードの詳細を表示する

リソースの infolist、またはその一部を再利用します。

```php
// CustomerResource
public static function relationPageHeader(Schema $schema): Schema
{
    return static::infolist($schema);
}
```

## 詳細ページのないリソース

リレーションページは一覧ページだけでも動作します。テーブルからリンクしてください。

```php
Action::make('orders')
    ->url(fn (Customer $record): string => CustomerResource::getUrl('relations.orders', ['record' => $record]))
```

代わりとなる詳細ページがないため、レコードの `view` ポリシーが必要です。

## 別のキーで登録した詳細ページ

`'details' => ViewCustomer::route('/{record}')` はクラスで見つかります。独自のリレーションページクラスを使う場合は、名前で指定することもできます：`protected static ?string $contextPage = 'details';`。

## どこからでもリレーションページにリンクする

```php
CustomerResource::getUrl('relations.orders', ['record' => $customer]);
```

テナント、クラスター、ネストされたリソースの中では、他のリソースの URL と同じように、Filament が足りないパラメーターを現在のリクエストから補います。

## 控えめなメニュー

1.2 のメニューです。パンくずリストからシンプルなリストが開き、検索、バッジ、ショートカットはありません。

```php
'menu' => [
    'preset' => 'simple',
    'searchable' => false,
    'badges' => false,
    'shortcut' => null,
],
```

## スタッフだけに表示する

リソースで、レコードごとや一部のユーザーに対してリレーションページを非表示にします。

```php
public static function canAccessRelationPage(Model $record, string $slug): bool
{
    return $slug !== 'audit-logs' || auth()->user()->isStaff();
}
```

## 複数のパネルやテナント

何もする必要はありません。ルートはリソースを持つすべてのパネルに、そのテナントとクラスターの中で登録されます。前後のレコードは、パネル、テナント、リソースごとに 1 つの一覧を記憶します。
