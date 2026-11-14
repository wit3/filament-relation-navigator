---
layout: home

hero:
  name: Relation Navigator
  text: Настоящая страница для каждого relation manager
  tagline: У каждого relation manager своя страница и свой URL. Меню с поиском в хлебных крошках переключает между ними. Для Filament 4 и 5.
  image:
    light: https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-light.png
    dark: https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-dark.png
    alt: Заказы клиента на отдельной странице, меню в хлебных крошках открыто
  actions:
    - theme: brand
      text: Начать
      link: /ru/guide/quick-start
    - theme: alt
      text: Открыть на GitHub
      link: https://github.com/wit3/filament-relation-navigator

features:
  - title: Один трейт, никаких классов страниц
    details: Добавьте HasRelationPages в ресурс. Ничего не нужно генерировать и ничего добавлять в getPages() или в panel provider.
  - title: Relation manager, который у вас уже есть
    details: Та же таблица, фильтры, действия, бейджи и права доступа, на собственном маршруте, на который можно дать ссылку, добавить в закладки и открыть в новой вкладке.
  - title: Меню в хлебных крошках
    details: Все страницы записи, с иконками, бейджами, поиском, навигацией с клавиатуры и сочетанием клавиш. Два пресета или своё представление.
  - title: Шапка, действия и виджеты
    details: Карточка записи, виджеты и действия в шапке над связью, взятые из ресурса.
  - title: Предыдущая и следующая запись
    details: Переходите по записям, оставаясь на той же странице, в порядке списка, из которого вы пришли.
  - title: Всё включается где угодно
    details: Каждую функцию можно включить или выключить глобально, для ресурса и для страницы или relation manager.
---

<div class="home-section">

## От вкладки к странице

Вкладки под страницей просмотра становятся отдельными страницами. Тот же relation manager, та же таблица, URL, которым можно поделиться:

| До | После |
| --- | --- |
| `/customers/1?relation=orders` | `/customers/1/orders` |
| `/customers/1?relation=invoices` | `/customers/1/invoices` |

Достаточно двух трейтов: см. [быстрый старт](/ru/guide/quick-start).

<img class="demo" src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/demo.gif" alt="Меню открыто по Alt Shift R и отфильтровано по Invoices, затем следующий клиент на той же странице">

</div>

<style>
.home-section { max-width: 1152px; margin: 64px auto 0; padding: 0 24px; }
.home-section h2 { border-top: 0; margin-top: 0; padding-top: 0; }
.home-section .demo { width: 100%; margin-top: 24px; border-radius: 12px; border: 1px solid var(--vp-c-divider); }
</style>
