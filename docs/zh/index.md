---
layout: home

hero:
  name: Relation Navigator
  text: 为每个 relation manager 提供真正的页面
  tagline: 每个 relation manager 都有自己的页面和 URL。面包屑中带搜索的菜单可以在它们之间切换。适用于 Filament 4 和 5。
  image:
    light: https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-light.png
    dark: https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-dark.png
    alt: 一个客户的订单显示在独立的页面上，面包屑菜单已展开
  actions:
    - theme: brand
      text: 开始使用
      link: /zh/guide/quick-start
    - theme: alt
      text: 在 GitHub 上查看
      link: https://github.com/wit3/filament-relation-navigator

features:
  - title: 一个 trait，无需页面类
    details: 在 resource 中添加 HasRelationPages。不用生成任何东西，也不用往 getPages() 或 panel provider 里添加内容。
  - title: 你已有的 relation manager
    details: 同样的表格、筛选器、操作、徽章和权限控制，放在独立的路由上，可以链接、加入书签，也可以在新标签页中打开。
  - title: 面包屑中的菜单
    details: 记录的所有页面，带有图标、徽章、搜索、键盘导航和快捷键。两种预设，也可以使用你自己的视图。
  - title: 页头、操作和 widget
    details: 在关联上方显示记录的卡片、widget 和页头操作，内容取自 resource。
  - title: 上一条和下一条记录
    details: 停留在同一页面上切换记录，顺序与你进入时所在的列表一致。
  - title: 任何功能，随处开关
    details: 每个功能都可以全局、按 resource、按页面或按 relation manager 开启或关闭。
---

<div class="home-section">

## 从标签页到页面

查看页面下方的标签页变成了独立的页面。同样的 relation manager，同样的表格，还有一个可以分享的 URL：

| 之前 | 之后 |
| --- | --- |
| `/customers/1?relation=orders` | `/customers/1/orders` |
| `/customers/1?relation=invoices` | `/customers/1/invoices` |

只需要两个 trait：参见[快速开始](/zh/guide/quick-start)。

<img class="demo" src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/demo.gif" alt="用 Alt Shift R 打开菜单，筛选出 Invoices，然后在同一页面上切换到下一个客户">

</div>

<style>
.home-section { max-width: 1152px; margin: 64px auto 0; padding: 0 24px; }
.home-section h2 { border-top: 0; margin-top: 0; padding-top: 0; }
.home-section .demo { width: 100%; margin-top: 24px; border-radius: 12px; border: 1px solid var(--vp-c-divider); }
</style>
