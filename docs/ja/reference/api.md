# API リファレンス

以下はすべて公開 API で、[セマンティックバージョニング](/ja/reference/upgrade)の対象です。1.x の間は互換性を壊す形で変更されません。ここに載っていないものは内部用です。

## トレイト

| トレイト | 使う場所 | 役割 |
|---|---|---|
| `Concerns\HasRelationPages` | リソース | すべてのリレーションマネージャーが専用のページを持つ |
| `Concerns\InteractsWithRelationPage` | リレーションマネージャー | このリレーションマネージャーがページを持つ。下記の任意の static プロパティを読み取る |
| `Concerns\HasRelationNavigator` | レコードのページ（詳細、編集、カスタム） | パンくずリストのメニューを表示し、このページもメニューに載る |

すべて `Wit3\FilamentRelationNavigator` 名前空間にあります。

## リソース側

すべて static で、すべて任意です。

| メソッド | 用途 |
|---|---|
| `hasRelationPage(string $slug): bool` | リレーションを除外する |
| `canAccessRelationPage(Model $record, string $slug): bool` | レコードごとにリレーションページを拒否する（ページ、メニュー、グローバル検索、リダイレクト） |
| `relationPageHeader(Schema $schema): Schema` | すべてのリレーションページで、リレーションの上に表示するヘッダー |
| `getRelationPageHeaderActions(): array` | リレーションページのヘッダーアクション |
| `getRelationPageHeaderWidgets(): array`、`getRelationPageFooterWidgets(): array` | リレーションページのウィジェット（`$record` を受け取る） |
| `hasRelationPageLinks(): bool` | そのリレーションマネージャーの「ページとして開く」 |
| `configureRelationPageAction(Action $action, RelationManager $manager): Action` | このリソースでそのアクションを変更する |
| `hasRelationPageGlobalSearchActions(): bool` | このリソースのグローバル検索のリンク |
| 任意の[オプションメソッド](/ja/reference/configuration#options)（static） | リソースのすべてのページに対するオプション |

## リレーションマネージャー側

すべて static で、すべて任意です。プロパティには `InteractsWithRelationPage` が必要ですが、メソッドはなくても動作します。

| プロパティまたはメソッド | 用途 |
|---|---|
| `$hasRelationPage`、`hasRelationPage(): bool` | このリレーションマネージャーにページを用意するかどうか |
| `$relationPageSlug`、`getRelationPageSlug(): ?string` | そのページのスラッグ |
| `$hasRelationPageLink`、`hasRelationPageLink(): bool` | そのテーブルの「ページとして開く」 |
| `$hasRelationPageGlobalSearchAction`、`hasRelationPageGlobalSearchAction(): bool` | グローバル検索の結果の下に表示するそのリンク |
| `$hasRelationNavigatorBadge`、`hasRelationNavigatorBadge(): bool` | メニューでのそのバッジ（タブのバッジはそのまま） |
| `getRelationPageTitle(Model $ownerRecord)` | そのページのタイトル。`null` でデフォルトのまま |
| `getRelationPageSubheading(Model $ownerRecord)` | そのページのサブ見出し |
| `getRelationPageBreadcrumb(Model $ownerRecord): ?string` | そのページのパンくずリストの最後の項目 |
| `relationPageHeader()`、`getRelationPageHeaderActions()`、`getRelationPageHeaderWidgets()`、`getRelationPageFooterWidgets()` | リソースと同じ。ただしそのページだけ |
| `configureRelationPageAction(Action $action, RelationManager $manager): Action` | その「ページとして開く」アクションを変更する |

Filament 自身の `isBadgeLazyLoaded()`（または `$isBadgeLazyLoaded`）でも、メニューのバッジが遅延読み込みになります。

## レコードのページ側（`HasRelationNavigator`）

ページでどれでもオーバーライドできます。

| メソッド | 用途 |
|---|---|
| [オプションメソッド](/ja/reference/configuration#options) | このページのオプション |
| `getRelationNavigatorRecordNavigationQuery(): ?Builder` | 前後のレコードの移動対象 |
| `withRelationNavigator(array $breadcrumbs): array` | 自分で組み立てたパンくずリストにメニューを追加する |

## 独自のリレーションページクラス

`Pages\RelationPage` を継承し（または `php artisan make:filament-relation-navigator CustomerResource` を実行し）、`getPages()` に `...CustomerRelationPage::relationRoutes()` を展開します。

| メンバー | 用途 |
|---|---|
| `protected static ?array $relations` | これらのスラッグだけ |
| `protected static array $excludedRelations` | これらのスラッグは除外 |
| `protected static ?string $contextPage` | 代わりとなるページのキー（デフォルトは詳細ページ、なければ編集ページ） |
| `$title`、`$subheading`、`$breadcrumb`、および Filament のページのすべてのメソッド | 通常の Filament ページと同じ |

## ヘルパー

`RelationNavigator` の static メソッドです。

| メソッド | 用途 |
|---|---|
| `getItems(string $resource, Model $record, ...)` | レコードのメニュー項目 |
| `getRecordSubNavigation(Page $page): array` | 同じ項目を Filament のサブナビゲーションとして返す |
| `getRelationManagerPageUrl(RelationManager $manager): ?string` | リレーションマネージャーのページ。独自のボタン用 |
| `getGlobalSearchResultActions(string $resource, Model $record): array` | グローバル検索のリンク。独自のものにマージする |
| `configureRelationPageActionUsing(Closure $callback): void` | すべての「ページとして開く」アクションを変更する |
| `makeRelationPageAction(RelationManager $manager): Action` | そのアクションを作成する |
| `getRelationPageClass(string $resource): ?string` | リソースのリレーションページクラス |
| `RELATION_PAGE_ACTION` | 「ページとして開く」アクションの名前 |

`RelationNavigatorItem`（`$items` 内）：`key`、`label`、`url`、`icon`、`badge`、`badgeColor`、`isActive`、`isBadgeDeferred`、`isRelationPage()`。

## ルート、ビュー、コマンド

- ルート名：`{リソースのルートのベース}.relations.{slug}`。`getUrl('relations.orders', [...])` で使います。
- ビュー：`filament-relation-navigator::menu.github`、`menu.simple`、`menu.mobile`、`menu.partials.list`、`menu.partials.trigger-attributes`。変数は[独自のプリセット](/ja/guide/menu-presets#%E7%8B%AC%E8%87%AA%E3%81%AE%E3%83%95%E3%82%9A%E3%83%AA%E3%82%BB%E3%83%83%E3%83%88)に一覧があります。
- コマンド：`make:filament-relation-navigator {resource}`、`make:filament-relation-navigator-preset {name} --from=github|simple`。
- 翻訳：`filament-relation-navigator::navigator.*`。
