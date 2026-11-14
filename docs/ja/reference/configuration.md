# 設定

すべてのリソースのデフォルトを変更するには、設定ファイルを公開します。

```bash
php artisan vendor:publish --tag=filament-relation-navigator-config
```

公開した設定ファイルは、マイナーバージョンをまたいでもそのまま動作します。すべてのキーにはコード内にデフォルト値があるので、新しいキーは変更したいときだけ追加すれば十分です。

## オプション {#options}

ほとんどのオプションは、**リソース単位**（リソースの static メソッド）や、**ページまたはリレーションマネージャー単位**でも設定できます。

- **メニューのオプション**（メソッドのある `menu.*`、前後のレコード、タブのリダイレクト）：ページが最優先で、次にリソース、最後に設定です。
- 「**ページとして開く**」と**リレーションマネージャーのメニューのバッジ**：すべてのレベルが許可している場合にだけ表示されます。どのレベルでも無効にできます。
- **グローバル検索のリンク**：そのリソースでは、リソースのメソッドが設定を置き換えます。リレーションマネージャーは自分を除外することしかできません。
- **前後のレコードのために記憶する一覧**：リソースまたは設定だけで決まります。一覧ページからはレコードページに問い合わせられないためです。
- `menu.presets`、`menu.search_from`、`menu.max_height`、`menu.separator` は設定ファイルでのみ指定できます。

| キー | デフォルト | 役割 | 他のレベル |
|---|---|---|---|
| `menu.preset` | `'github'` | メニューの見た目：`github`、`simple`、独自のプリセット、またはビュー名 | `getRelationNavigatorPreset()` |
| `menu.presets` | `simple`、`github` | プリセットの名前とビュー | |
| `menu.searchable` | `true` | リストの上の検索フィールド | `isRelationNavigatorSearchable()` |
| `menu.search_from` | `0` | 項目がこの数以上のときに検索フィールドを表示 | |
| `menu.badges` | `'lazy'` | `'lazy'`：メニューを開いたときにバッジを読み込む。`true`：ページと一緒。`false`：なし | `hasRelationNavigatorBadges()`、`hasDeferredRelationNavigatorBadges()`。リレーションマネージャー：`hasRelationNavigatorBadge()` |
| `menu.max_height` | `'24rem'` | スクロールが始まるまでのリストの高さ（CSS の長さ） | |
| `menu.separator` | `true` | レコードのページとリレーションページを 2 つのブロックに分ける | |
| `menu.mobile` | `true` | 小さな画面では見出しの上にメニューを表示 | `hasRelationNavigatorMobileMenu()` |
| `menu.shortcut` | `'alt+shift+r'` | メニューを開くキーボードショートカット。`null`：なし | `getRelationNavigatorShortcut()` |
| `relation_manager_link` | `true` | リレーションマネージャーのテーブルの「ページとして開く」アクション | リソース `hasRelationPageLinks()`、リレーションマネージャー `hasRelationPageLink()` |
| `relation_manager_action` | 矢印付きのリンク | そのアクションの見た目：`style`、`label`、`icon`、`icon_position`、`color`、`size`、`new_tab` | `configureRelationPageActionUsing()`、`configureRelationPageAction()` |
| `global_search_actions` | `false` | グローバル検索の結果の下に表示するリレーションページへのリンク：`true` または結果ごとの最大数 | リソース `hasRelationPageGlobalSearchActions()`、リレーションマネージャー `hasRelationPageGlobalSearchAction()` |
| `record_navigation` | `false` | 前後のレコードのボタン | `hasRelationNavigatorRecordNavigation()` |
| `record_navigation_follows_list` | `true` | 前後のレコードが、ユーザーが来た一覧に従う | `shouldRelationNavigatorRecordNavigationFollowList()` |
| `redirect_relation_tabs` | `false` | 詳細ページの `?relation=x` でリレーションページを開く | `shouldRedirectRelationNavigatorTabs()` |

ページのメソッドは、`HasRelationNavigator` を使うレコードのページのインスタンスメソッドです。リソースでは同じメソッドが static になり、そのすべてのページに適用されます。[ページ、リソース、設定](/ja/guide/menu-presets#%E3%83%98%E3%82%9A%E3%83%BC%E3%82%B7%E3%82%99%E3%80%81%E3%83%AA%E3%82%BD%E3%83%BC%E3%82%B9%E3%80%81%E8%A8%AD%E5%AE%9A)を参照してください。

## ファイル全体

現在のバージョンに含まれているものです（コメントは英語です）。

<<< ../../../config/filament-relation-navigator.php
