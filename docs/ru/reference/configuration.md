# Конфигурация

Опубликуйте файл конфига, чтобы изменить значения по умолчанию для всех ресурсов:

```bash
php artisan vendor:publish --tag=filament-relation-navigator-config
```

Опубликованный конфиг продолжает работать в минорных версиях: у каждого ключа есть значение по умолчанию в коде, поэтому новые ключи нужно добавлять, только когда вы хотите их изменить.

## Опции

Большинство опций можно задать также **для ресурса** (статический метод на ресурсе) или **для страницы или relation manager**:

- **Опции меню** (`menu.*` с методом, навигация по записям, редирект со вкладок): приоритет у страницы, затем у ресурса, затем у конфига.
- **«Открыть как страницу»** и **бейдж relation manager в меню**: показываются, только если это разрешают все уровни; отключить может любой уровень.
- **Ссылки в глобальном поиске**: метод ресурса заменяет конфиг для этого ресурса; relation manager может только исключить себя.
- **Список, который запоминается для «предыдущей» и «следующей»**: решают только ресурс или конфиг, потому что страница списка не может спросить страницу записи.
- `menu.presets`, `menu.search_from`, `menu.max_height` и `menu.separator` задаются только в конфиге.

| Ключ | По умолчанию | Что делает | Другие уровни |
|---|---|---|---|
| `menu.preset` | `'github'` | Вид меню: `github`, `simple`, ваш пресет или имя представления | `getRelationNavigatorPreset()` |
| `menu.presets` | `simple`, `github` | Имена и представления пресетов | |
| `menu.searchable` | `true` | Поле поиска над списком | `isRelationNavigatorSearchable()` |
| `menu.search_from` | `0` | Показывать поле поиска начиная с этого числа элементов | |
| `menu.badges` | `'lazy'` | `'lazy'`: бейджи загружаются при открытии меню; `true`: вместе со страницей; `false`: без бейджей | `hasRelationNavigatorBadges()`, `hasDeferredRelationNavigatorBadges()`; relation manager: `hasRelationNavigatorBadge()` |
| `menu.max_height` | `'24rem'` | Высота списка, после которой появляется прокрутка (длина CSS) | |
| `menu.separator` | `true` | Страницы записи и страницы связей двумя блоками | |
| `menu.mobile` | `true` | Меню над заголовком на маленьких экранах | `hasRelationNavigatorMobileMenu()` |
| `menu.shortcut` | `'alt+shift+r'` | Сочетание клавиш, которое открывает меню; `null`: без сочетания | `getRelationNavigatorShortcut()` |
| `relation_manager_link` | `true` | Действие «Открыть как страницу» в таблицах relation manager | ресурс `hasRelationPageLinks()`, relation manager `hasRelationPageLink()` |
| `relation_manager_action` | ссылка со стрелкой | Вид этого действия: `style`, `label`, `icon`, `icon_position`, `color`, `size`, `new_tab` | `configureRelationPageActionUsing()`, `configureRelationPageAction()` |
| `global_search_actions` | `false` | Ссылки на страницы связей под результатами глобального поиска: `true` или наибольшее число на результат | ресурс `hasRelationPageGlobalSearchActions()`, relation manager `hasRelationPageGlobalSearchAction()` |
| `record_navigation` | `false` | Кнопки предыдущей и следующей записи | `hasRelationNavigatorRecordNavigation()` |
| `record_navigation_follows_list` | `true` | «Предыдущая» и «следующая» следуют списку, из которого пришёл пользователь | `shouldRelationNavigatorRecordNavigationFollowList()` |
| `redirect_relation_tabs` | `false` | `?relation=x` на странице просмотра открывает страницу связи | `shouldRedirectRelationNavigatorTabs()` |

Методы страницы — это методы экземпляра на странице записи с `HasRelationNavigator`; на ресурсе тот же метод статический и действует для всех его страниц. См. [Страница, ресурс или конфиг](/ru/guide/menu-presets#страница-ресурс-или-конфиг).

## Файл целиком

В том виде, в каком он поставляется с текущей версией (комментарии на английском):

<<< ../../../config/filament-relation-navigator.php
