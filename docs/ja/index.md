---
layout: home

hero:
  name: Relation Navigator
  text: リレーションマネージャーごとに本物のページを
  tagline: すべてのリレーションマネージャーが専用のページと URL を持ちます。パンくずリストの検索付きメニューでページ間を移動できます。Filament 4 と 5 に対応。
  image:
    light: https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-light.png
    dark: https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-dark.png
    alt: 顧客の注文を専用ページに表示し、パンくずリストのメニューを開いた状態
  actions:
    - theme: brand
      text: はじめる
      link: /ja/guide/quick-start
    - theme: alt
      text: GitHub で見る
      link: https://github.com/wit3/filament-relation-navigator

features:
  - title: トレイト 1 つ、ページクラスは不要
    details: リソースに HasRelationPages を追加するだけです。生成するものも、getPages() やパネルプロバイダーへの追記もありません。
  - title: すでにあるリレーションマネージャーをそのまま
    details: テーブル、フィルター、アクション、バッジ、認可はそのままで、リンクやブックマークができ、新しいタブでも開ける専用のルートで表示します。
  - title: パンくずリストのメニュー
    details: レコードのすべてのページを、アイコン、バッジ、検索、キーボード操作、ショートカット付きで表示します。2 つのプリセットか、独自のビューを使えます。
  - title: ヘッダー、アクション、ウィジェット
    details: リソースから取得したレコードのカード、ウィジェット、ヘッダーアクションを、リレーションの上に表示します。
  - title: 前後のレコード
    details: 同じページのまま、来た一覧の並び順でレコードを移動できます。
  - title: どこでも、何でも切り替え可能
    details: すべての機能を、グローバル、リソース単位、ページまたはリレーションマネージャー単位で有効・無効にできます。
---

<div class="home-section">

## タブからページへ

詳細ページの下にあるタブが、それぞれ専用のページになります。リレーションマネージャーもテーブルも同じまま、共有できる URL が手に入ります。

| 変更前 | 変更後 |
| --- | --- |
| `/customers/1?relation=orders` | `/customers/1/orders` |
| `/customers/1?relation=invoices` | `/customers/1/invoices` |

必要なのはトレイト 2 つだけです。[クイックスタート](/ja/guide/quick-start)を参照してください。

<img class="demo" src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/demo.gif" alt="Alt Shift R でメニューを開いて Invoices で絞り込み、続いて同じページで次の顧客へ移動">

</div>

<style>
.home-section { max-width: 1152px; margin: 64px auto 0; padding: 0 24px; }
.home-section h2 { border-top: 0; margin-top: 0; padding-top: 0; }
.home-section .demo { width: 100%; margin-top: 24px; border-radius: 12px; border: 1px solid var(--vp-c-divider); }
</style>
