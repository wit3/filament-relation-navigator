# Справочник API

Всё, что ниже, — публичный API, на который распространяется [семантическое версионирование](/ru/reference/upgrade): в пределах 1.x он не меняется несовместимым образом. Всё, чего здесь нет, — внутреннее.

## Трейты

| Трейт | Где использовать | Что делает |
|---|---|---|
| `Concerns\HasRelationPages` | ресурс | У каждого relation manager своя страница |
| `Concerns\InteractsWithRelationPage` | relation manager | У этого relation manager есть страница; читает необязательные статические свойства ниже |
| `Concerns\HasRelationNavigator` | страница записи (просмотр, редактирование, своя) | Меню в хлебных крошках, и страница попадает в него |

Все в пространстве имён `Wit3\FilamentRelationNavigator`.

## На ресурсе

Все статические, все необязательные.

| Метод | Назначение |
|---|---|
| `hasRelationPage(string $slug): bool` | Исключить связь |
| `canAccessRelationPage(Model $record, string $slug): bool` | Запретить страницу связи для записи (страницы, меню, глобальный поиск, редирект) |
| `relationPageHeader(Schema $schema): Schema` | Шапка над связью на всех страницах связей |
| `getRelationPageHeaderActions(): array` | Действия в шапке страниц связей |
| `getRelationPageHeaderWidgets(): array`, `getRelationPageFooterWidgets(): array` | Виджеты страниц связей (получают `$record`) |
| `hasRelationPageLinks(): bool` | «Открыть как страницу» в его relation manager |
| `configureRelationPageAction(Action $action, RelationManager $manager): Action` | Изменить это действие для этого ресурса |
| `hasRelationPageGlobalSearchActions(): bool` | Ссылки в глобальном поиске для этого ресурса |
| любой [метод опции](/ru/reference/configuration#опции), static | Опция для всех страниц ресурса |

## В relation manager

Все статические, все необязательные. Свойствам нужен `InteractsWithRelationPage`; методы работают и без него.

| Свойство или метод | Назначение |
|---|---|
| `$hasRelationPage`, `hasRelationPage(): bool` | Есть ли страница у этого relation manager |
| `$relationPageSlug`, `getRelationPageSlug(): ?string` | Слаг его страницы |
| `$hasRelationPageLink`, `hasRelationPageLink(): bool` | «Открыть как страницу» в его таблице |
| `$hasRelationPageGlobalSearchAction`, `hasRelationPageGlobalSearchAction(): bool` | Его ссылка под результатами глобального поиска |
| `$hasRelationNavigatorBadge`, `hasRelationNavigatorBadge(): bool` | Его бейдж в меню (во вкладке бейдж остаётся) |
| `getRelationPageTitle(Model $ownerRecord)` | Заголовок его страницы; `null` оставляет значение по умолчанию |
| `getRelationPageSubheading(Model $ownerRecord)` | Подзаголовок его страницы |
| `getRelationPageBreadcrumb(Model $ownerRecord): ?string` | Последний элемент хлебных крошек его страницы |
| `relationPageHeader()`, `getRelationPageHeaderActions()`, `getRelationPageHeaderWidgets()`, `getRelationPageFooterWidgets()` | Как на ресурсе, только для его страницы |
| `configureRelationPageAction(Action $action, RelationManager $manager): Action` | Изменить его действие «Открыть как страницу» |

Собственный `isBadgeLazyLoaded()` Filament (или `$isBadgeLazyLoaded`) тоже делает его бейдж в меню ленивым.

## На странице записи (`HasRelationNavigator`)

Переопределите любой из них на странице:

| Метод | Назначение |
|---|---|
| [методы опций](/ru/reference/configuration#опции) | Опции этой страницы |
| `getRelationNavigatorRecordNavigationQuery(): ?Builder` | Записи, по которым ходят «предыдущая» и «следующая» |
| `withRelationNavigator(array $breadcrumbs): array` | Добавить меню в хлебные крошки, которые вы строите сами |

## Свой класс страницы связи

Наследуйте `Pages\RelationPage` (или запустите `php artisan make:filament-relation-navigator CustomerResource`) и добавьте `...CustomerRelationPage::relationRoutes()` в `getPages()`.

| Член класса | Назначение |
|---|---|
| `protected static ?array $relations` | Только эти слаги |
| `protected static array $excludedRelations` | Никогда эти слаги |
| `protected static ?string $contextPage` | Ключ страницы, которую она заменяет (по умолчанию страница просмотра, иначе страница редактирования) |
| `$title`, `$subheading`, `$breadcrumb` и любой метод страницы Filament | Как на любой странице Filament |

## Вспомогательные методы

Статические методы `RelationNavigator`:

| Метод | Назначение |
|---|---|
| `getItems(string $resource, Model $record, ...)` | Элементы меню записи |
| `getRecordSubNavigation(Page $page): array` | Те же элементы в виде sub-navigation Filament |
| `getRelationManagerPageUrl(RelationManager $manager): ?string` | Страница relation manager, для ваших собственных кнопок |
| `getGlobalSearchResultActions(string $resource, Model $record): array` | Ссылки глобального поиска, чтобы добавить их к своим |
| `configureRelationPageActionUsing(Closure $callback): void` | Изменить все действия «Открыть как страницу» |
| `makeRelationPageAction(RelationManager $manager): Action` | Создать это действие |
| `getRelationPageClass(string $resource): ?string` | Класс страницы связи ресурса |
| `RELATION_PAGE_ACTION` | Имя действия «Открыть как страницу» |

`RelationNavigatorItem` (в `$items`): `key`, `label`, `url`, `icon`, `badge`, `badgeColor`, `isActive`, `isBadgeDeferred`, `isRelationPage()`.

## Маршруты, представления и команды

- Имена маршрутов: `{база маршрутов ресурса}.relations.{slug}`, для `getUrl('relations.orders', [...])`.
- Представления: `filament-relation-navigator::menu.github`, `menu.simple`, `menu.mobile`, `menu.partials.list`, `menu.partials.trigger-attributes`. Их переменные перечислены в разделе [Свой пресет](/ru/guide/menu-presets#свои-пресет).
- Команды: `make:filament-relation-navigator {resource}`, `make:filament-relation-navigator-preset {name} --from=github|simple`.
- Переводы: `filament-relation-navigator::navigator.*`.
