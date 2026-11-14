<img class="filament-hidden" src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/cover.jpg" alt="Relation Navigator for Filament">

# Relation Navigator for Filament

<p class="filament-hidden">
    <a href="https://packagist.org/packages/wit3/filament-relation-navigator"><img src="https://img.shields.io/packagist/v/wit3/filament-relation-navigator.svg?style=flat-square" alt="Latest version on Packagist"></a>
    <a href="https://github.com/wit3/filament-relation-navigator/actions/workflows/tests.yml"><img src="https://img.shields.io/github/actions/workflow/status/wit3/filament-relation-navigator/tests.yml?branch=main&label=tests&style=flat-square" alt="Tests"></a>
    <a href="https://packagist.org/packages/wit3/filament-relation-navigator"><img src="https://img.shields.io/packagist/dt/wit3/filament-relation-navigator.svg?style=flat-square" alt="Total downloads"></a>
    <a href="LICENSE.md"><img src="https://img.shields.io/packagist/l/wit3/filament-relation-navigator.svg?style=flat-square" alt="License"></a>
</p>

**[Read the documentation →](https://wit3.github.io/filament-relation-navigator/)** Guides, configuration reference, upgrade guide and search.

<!-- #region intro -->
Give every relation manager of a Filament resource **its own page**, and jump
between them from a **menu in the breadcrumb**.

- `/customers/1?relation=orders` becomes **`/customers/1/orders`**: a real
  route you can link, bookmark and open in a new tab.
- The page renders the **relation manager you already have**: same table,
  filters, actions, badges and authorization. Nothing is duplicated.
- Add a **header** above the relation (the record's card, widgets, actions),
  taken from the resource.
- The last breadcrumb opens a **menu** with the record's pages and every
  relation the user can see, with icons and badges. Two looks, optional
  search and keyboard navigation, a view of your own, and a version for
  small screens.
- **Previous and next record** on the same page, an **"Open as page"** action
  on every relation manager table, and links to old tabs that land on the new
  pages: each one switchable globally, per resource or per page.
- **One trait, no page classes**: no files to generate, nothing to add to
  `getPages()` or to the panel provider.

<img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/demo.gif" alt="The menu opened with Alt Shift R, filtered to Invoices, then the next customer on the same page" width="880">
<!-- #endregion intro -->

## Requirements

<!-- #region requirements -->
| Package  | Version              |
| -------- | -------------------- |
| PHP      | 8.2+                 |
| Laravel  | 12, 13               |
| Filament | 4.x, 5.x             |
<!-- #endregion requirements -->

## Installation

<!-- #region installation -->
```bash
composer require wit3/filament-relation-navigator
php artisan filament:assets
```

Run `filament:assets` again after each update: Filament copies the plugin's
stylesheet into `public/`.
<!-- #endregion installation -->

## Quick start

<!-- #region quick-start -->
**1. Give the relations a page.** Add the trait to the resource:

```php
use Wit3\FilamentRelationNavigator\Concerns\HasRelationPages;

class CustomerResource extends Resource
{
    use HasRelationPages;

    public static function getRelations(): array
    {
        return [
            'orders' => OrdersRelationManager::class,
            'invoices' => InvoicesRelationManager::class,
            NotesRelationManager::class,
        ];
    }
}
```

Every relation now has its page: `/customers/1/orders`, `/customers/1/invoices`,
`/customers/1/notes`.

**2. Add the menu to the view page:**

```php
use Wit3\FilamentRelationNavigator\Concerns\HasRelationNavigator;

class ViewCustomer extends ViewRecord
{
    use HasRelationNavigator;
}
```

That's all. The relation pages have the menu already, and the tabs on the
view page keep working as before.

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-light.png" alt="The orders of a customer on their own page, with the breadcrumb menu open">
</picture>
<!-- #endregion quick-start -->

## Relation pages

<!-- #region relation-pages -->
### Opting in

Opt in on the resource, for every relation manager, or only on the relation
managers you want:

```php
use Wit3\FilamentRelationNavigator\Concerns\InteractsWithRelationPage;

class OrdersRelationManager extends RelationManager
{
    use InteractsWithRelationPage;
}
```

| Setup                                       | Relations with a page               |
| ------------------------------------------- | ----------------------------------- |
| `HasRelationPages` on the resource          | all, minus the ones that opt out    |
| `InteractsWithRelationPage` on a manager    | only the managers that opt in       |
| A page class you wrote (see below)          | all, minus the ones that opt out    |

To leave some relations out:

```php
// On the resource
public static function hasRelationPage(string $slug): bool
{
    return $slug !== 'notes';
}

// On a relation manager, with InteractsWithRelationPage
protected static bool $hasRelationPage = false;
```

A relation manager shared by two resources opts in for both: use the
resource's `hasRelationPage()` to leave one of them out.

### URLs and slugs

| `getRelations()` entry                          | URL                         |
| ----------------------------------------------- | --------------------------- |
| `'orders' => OrdersRelationManager::class`      | `/customers/1/orders`       |
| `SupportTicketsRelationManager::class` (no key) | `/customers/1/support-tickets` |
| `RelationGroup::make('Activity', [...])`        | `/customers/1/activity`     |

The key in `getRelations()` wins; without a key, the slug is the kebab-cased
relationship name (or the group label). A relation manager can pick its own
with `protected static ?string $relationPageSlug = 'tickets';`. Slugs equal to
one of the resource's page keys (`edit`, `view`...) are skipped.

Each page is a named route, `relations.{slug}`:

```php
CustomerResource::getUrl('relations.orders', ['record' => $customer]);
```

### Authorization and behaviour

The relation manager receives the resource's **view** page as `$pageClass`
(the edit page when there is no view page), so `isReadOnly()`,
`canViewForRecord()`, `getBadge()` and every other check behave exactly as in
the tab. A relation page needs what the view page needs (its `canAccess()`
and the record's `view` policy; `update` when it stands for the edit page)
and the manager's `canViewForRecord()`; an unknown slug is a 404. The menu
only lists the pages the user can open.
<!-- #endregion relation-pages -->

## Header, actions and widgets

<!-- #region header-actions-and-widgets -->
Above the relation, a relation page can show part of the record's view,
header actions and widgets. Define them on the resource for every relation
page, or on a relation manager for its own page:

```php
public static function relationPageHeader(Schema $schema): Schema
{
    // The whole infolist (static::infolist($schema)) or a piece of it.
    return $schema->components([
        CustomerCard::make(),
    ]);
}

public static function getRelationPageHeaderActions(): array
{
    return [
        Action::make('email')
            ->url(fn (Customer $record): string => "mailto:{$record->email}"),
    ];
}

public static function getRelationPageHeaderWidgets(): array
{
    return [CustomerStats::class]; // receives the record as $record
}

public static function getRelationPageFooterWidgets(): array
{
    return [];
}
```

- The header of a relation manager replaces the resource's one; actions and
  widgets of both are shown, the resource's first.
- Header actions act on the record the relation belongs to.
- With a header, the record's name is not repeated under the title.
- Pieces that only exist inside the view page class (its own methods and
  modals) are not reachable from here: move them to the resource, to an
  action class or to a trait.
<!-- #endregion header-actions-and-widgets -->

## The breadcrumb menu

<!-- #region the-breadcrumb-menu -->
`HasRelationNavigator` needs a page with a record: `ViewRecord`, `EditRecord`
and pages using `InteractsWithRecord` already have the two methods it
declares, `getRecord()` and `hasRecord()`. On a page without a record PHP
says so as soon as the class is loaded.

A record page is in the menu if, and only if, it uses `HasRelationNavigator`:
the view page, the edit page or any custom page of the record. On those pages
the last breadcrumb opens the menu. The relation pages are always listed,
after the record pages, with the relation managers' icons and badges, and
only when the user can see them.

If the page already builds its own breadcrumbs, pass them through
`withRelationNavigator()`:

```php
public function getBreadcrumbs(): array
{
    return $this->withRelationNavigator([
        CustomerResource::getUrl('index') => 'Customers',
        $this->getBreadcrumb(),
    ]);
}
```

A custom page that uses `InteractsWithRecord` itself, instead of extending
`ViewRecord` or `EditRecord`, has two `getBreadcrumbs()` methods. Unless it
defines its own, tell PHP which one wins:

```php
class CustomerActivity extends Page
{
    use HasRelationNavigator, InteractsWithRecord {
        HasRelationNavigator::getBreadcrumbs insteadof InteractsWithRecord;
        InteractsWithRecord::getBreadcrumbs as getRecordBreadcrumbs;
    }
}
```

The menu uses the page's `$navigationLabel` and `$navigationIcon`, and shows
it only when `canAccess()` allows it for the record.
<!-- #endregion the-breadcrumb-menu -->

## Open as page

<!-- #region open-as-page -->
On the view and edit pages, every relation manager that has its own page gets
an **Open as page** action in the header of its table, next to its title and
before its own actions. It opens the relation manager's own page, or the
page of its relation group when it has no page of its own. On a group's page
the managers with a page of their own get the action too; on its own page a
manager never links to itself. Turn it off where you need to:

```php
// config/filament-relation-navigator.php: everywhere
'relation_manager_link' => false,
```

```php
// On a resource: all its relation managers
public static function hasRelationPageLinks(): bool
{
    return false;
}
```

```php
// On a relation manager (with InteractsWithRelationPage)
protected static bool $hasRelationPageLink = false;

// or, without the trait
public static function hasRelationPageLink(): bool
{
    return false;
}
```

`RelationNavigator::getRelationManagerPageUrl($relationManager)` returns the
same URL, for a button of your own.
<!-- #endregion open-as-page -->

## Previous and next record

<!-- #region previous-and-next-record -->
Two buttons before the header actions open the previous or the next record,
by key, **on the same page**: from a customer's invoices to the next
customer's invoices. A record that cannot open that page opens on its first
page in the menu. They are off by default, since they add a few queries per
render:

```php
'record_navigation' => true,
```

They walk through the resource's own query (`getRecordRouteBindingEloquentQuery()`),
so tenancy and your scopes apply, and skip the records the user cannot view.
Per page:

```php
public function hasRelationNavigatorRecordNavigation(): bool
{
    return true;
}

public function getRelationNavigatorRecordNavigationQuery(): ?Builder
{
    return Customer::query()->where('active', true);
}
```

Nested resources are not supported yet: they show no buttons.
<!-- #endregion previous-and-next-record -->

## Links to relation manager tabs

<!-- #region links-to-relation-manager-tabs -->
Once the relation pages replace the tabs, old links such as
`/customers/1?relation=orders` can open `/customers/1/orders` instead:

```php
'redirect_relation_tabs' => true,
```

Tabs without a relation page, or that the record cannot see, stay where they
are.
<!-- #endregion links-to-relation-manager-tabs -->

## Global search

<!-- #region global-search -->
Global search results can link straight to the record's relation pages:
search a customer, jump to its orders. Off by default, since it runs the
relation managers' `canViewForRecord()` for every result:

```php
// config/filament-relation-navigator.php
'global_search_actions' => 3, // true for all of them, or the most links per result
```

```php
// On a resource: on or off for this resource only
public static function hasRelationPageGlobalSearchActions(): bool
{
    return true;
}
```

```php
// On a relation manager (with InteractsWithRelationPage): never linked
protected static bool $hasRelationPageGlobalSearchAction = false;
```

Resources with `HasRelationPages` get the links by themselves. A resource
that defines `getGlobalSearchResultActions()` itself, or that opts in only
from its relation managers, merges them in:

```php
public static function getGlobalSearchResultActions(Model $record): array
{
    return [
        // ...your actions
        ...RelationNavigator::getGlobalSearchResultActions(static::class, $record),
    ];
}
```

The links follow the same rules as the menu: only the relation pages the
user can open.
<!-- #endregion global-search -->

## Menu presets

<!-- #region menu-presets -->
Publish the config:

```bash
php artisan vendor:publish --tag=filament-relation-navigator-config
```

```php
'menu' => [
    'preset' => 'github',   // 'simple', a preset of yours, or a view name
    'presets' => [
        'simple' => 'filament-relation-navigator::menu.simple',
        'github' => 'filament-relation-navigator::menu.github',
    ],
    'searchable' => true,   // a search field on top of the list, whatever the preset
    'search_from' => 0,     // ...from this many items
    'badges' => true,       // the relation managers' badges; 'lazy' loads them when the menu opens
    'max_height' => '24rem',
    'separator' => true,    // record pages and relation pages in two blocks
    'mobile' => true,       // the menu above the heading on small screens
    'shortcut' => 'alt+shift+r', // opens the menu; null turns it off
],
```

**github** (the default): the last breadcrumb stays text and a caret next to
it opens a panel with a heading, a search field and a check on the current
page, like GitHub's repository switcher. The field gets the focus when the
menu opens and Enter opens the first match.

**simple**: the last breadcrumb itself opens the list. For the menu of 1.2
and earlier, set `'preset' => 'simple'` and `'searchable' => false`.

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/menu-simple-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/menu-simple-light.png" alt="The simple preset on the view page" width="520">
</picture>

### Deferred badges

Badges run the same queries as the tab badges. With `'badges' => 'lazy'` the
page renders without them, and the menu loads them in one request when it
opens.

A relation manager whose tab badge is lazy in Filament
(`protected static ?bool $isBadgeLazyLoaded = true;`, or `$isBadgeDeferred`
on Filament 4.13) gets the same treatment in the menu, whatever the config:
its badge is loaded when the menu opens, the others with the page.

If you published the package views, update `menu/partials/list.blade.php`
before relying on either.

**Keyboard**: from the search field, Down moves into the list; Up and Down
move between the pages, Enter opens one, Escape closes the menu.

### Shortcut and tooltip

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/tooltip-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/tooltip-light.png" alt="The tooltip of the menu button with its shortcut" width="470">
</picture>

The button that opens the menu has a tooltip with the shortcut, shown with
Mac or Windows/Linux keys. The shortcut opens the menu from anywhere on the
page, with the search field ready, like GitHub's repository switcher:
<kbd>⌥</kbd> <kbd>⇧</kbd> <kbd>R</kbd> on a Mac, <kbd>Alt</kbd> <kbd>Shift</kbd> <kbd>R</kbd>
elsewhere. Change it in the config or per page:

```php
'shortcut' => 'mod+shift+p', // mod is ⌘ on a Mac and Ctrl elsewhere
```

```php
public function getRelationNavigatorShortcut(): ?string
{
    return null; // no shortcut on this page
}
```

It is built not to get in the way:

- it matches the physical key, so it works with any keyboard layout and with
  Option on a Mac (where <kbd>⌥</kbd><kbd>⇧</kbd><kbd>R</kbd> types ‰);
- it does nothing while you type in a field;
- a shortcut of the panel's global search (`globalSearchKeyBindings()`) is
  never taken: the menu then has no shortcut;
- only the visible menu opens (breadcrumbs or small screens).

Supported keys: `alt`, `shift`, `ctrl`, `meta`, `mod` plus a letter, a digit
or `/ . , ;`.

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/menu-github-search-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/menu-github-search-light.png" alt="The github preset with the search field" width="700">
</picture>

### Page, resource or config

Every option has three levels. The page wins, then the resource, then the
config:

| Page method (static on the resource) | Config key |
|---|---|
| `getRelationNavigatorPreset(): string` | `menu.preset` |
| `isRelationNavigatorSearchable(): bool` | `menu.searchable` |
| `hasRelationNavigatorBadges(): bool` | `menu.badges` |
| `hasDeferredRelationNavigatorBadges(): bool` | `menu.badges` = `'lazy'` |
| `getRelationNavigatorShortcut(): ?string` | `menu.shortcut` |
| `hasRelationNavigatorMobileMenu(): bool` | `menu.mobile` |
| `hasRelationNavigatorRecordNavigation(): bool` | `record_navigation` |
| `shouldRedirectRelationNavigatorTabs(): bool` | `redirect_relation_tabs` |

```php
// On one page
public function isRelationNavigatorSearchable(): bool
{
    return false;
}
```

```php
// On a resource: the same method, static, for all its pages
public static function hasRelationNavigatorRecordNavigation(): bool
{
    return true;
}
```

### Small screens

Filament hides the breadcrumbs below 40rem. There the menu shows above the
page heading instead, on every page that uses `HasRelationNavigator`, with
the same preset options. Turn it off with `'mobile' => false`, or per page:

```php
public function hasRelationNavigatorMobileMenu(): bool
{
    return false;
}
```

<picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/mobile-dark.png">
    <img src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/mobile-light.png" alt="The menu on a phone, above the page heading" width="320">
</picture>

### Translations

English, Italian, Spanish, German, French, Brazilian Portuguese and Dutch.
Publish the translations to change them or add a language:

```bash
php artisan vendor:publish --tag=filament-relation-navigator-translations
```

### A preset of your own

Start from one of the package presets:

```bash
php artisan make:filament-relation-navigator-preset compact --from=github
```

It copies the preset to
`resources/views/filament/relation-navigator/compact.blade.php`. Register it
and change it as you like:

```php
'preset' => 'compact',
'presets' => [
    // ...
    'compact' => 'filament.relation-navigator.compact',
],
```

Any view name works as `preset` too. To change the package presets
themselves, publish their views:

```bash
php artisan vendor:publish --tag=filament-relation-navigator-views
```

A preset view receives:

| Variable             | Content                                                                 |
| -------------------- | ----------------------------------------------------------------------- |
| `$label`             | The last breadcrumb                                                     |
| `$items`             | The menu items: `key`, `label`, `url`, `icon`, `badge`, `badgeColor`, `isActive` |
| `$heading`           | "Switch page", translated                                               |
| `$isSearchable`      | Whether to show the search field                                        |
| `$hasSeparator`      | Whether to split record pages and relation pages in two blocks          |
| `$shortcut`          | The shortcut, or null                                                   |
| `$shortcutKeys`      | The shortcut keys to show: `['mac' => [...], 'other' => [...]]`         |
| `$searchPlaceholder` | "Find a page…", translated                                              |
| `$emptyLabel`        | "No pages found.", translated                                           |
| `$maxHeight`         | `menu.max_height`                                                       |

To give your button the tooltip and the shortcut, include
`filament-relation-navigator::menu.partials.trigger-attributes` inside its
tag. Each item also has `isRelationPage()`. To reuse the search field, the list
(with the separator and the keyboard navigation) and the "no results" line,
include `filament-relation-navigator::menu.partials.list` with `showCheck`.
<!-- #endregion menu-presets -->

## Customising the relation pages

<!-- #region customising-the-relation-pages -->
### Title, subheading and breadcrumb of one relation

A relation manager can set them for its own page, from the record, with no
page class:

```php
class OrdersRelationManager extends RelationManager
{
    public static function getRelationPageTitle(Model $ownerRecord): string|Htmlable|null
    {
        return "Orders of {$ownerRecord->name}";
    }

    public static function getRelationPageSubheading(Model $ownerRecord): string|Htmlable|null
    {
        return 'Last 12 months';
    }

    public static function getRelationPageBreadcrumb(Model $ownerRecord): ?string
    {
        return 'Orders';
    }
}
```

Return `null` to keep the default. A page class you wrote (below) wins with
its own `$title`, `$subheading` and `$breadcrumb`. The menu and the tabs keep
the relation manager's title. Relation groups have no relation manager, so
their page uses the group label.

### A page class of your own

The generated relation pages cover most needs. To change their title, their
breadcrumbs or anything else a Filament page can do, write the page class
yourself; the package then uses yours for that resource:

```bash
php artisan make:filament-relation-navigator CustomerResource
```

```php
class CustomerRelationPage extends RelationPage
{
    protected static string $resource = CustomerResource::class;

    protected static ?array $relations = ['orders', 'invoices'];  // only these
    protected static array $excludedRelations = ['notes'];         // never these
}
```

```php
public static function getPages(): array
{
    return [
        'index' => ListCustomers::route('/'),
        'view' => ViewCustomer::route('/{record}'),
        ...CustomerRelationPage::relationRoutes(),
    ];
}
```

The class is a regular Filament page: override `getTitle()`,
`getBreadcrumbs()`, `getHeaderActions()`, use your own traits, and so on.
<!-- #endregion customising-the-relation-pages -->

## Native record sub-navigation

<!-- #region native-record-sub-navigation -->
The same items can feed Filament's record sub-navigation:

```php
public static function getRecordSubNavigation(Page $page): array
{
    return RelationNavigator::getRecordSubNavigation($page);
}
```
<!-- #endregion native-record-sub-navigation -->

## How it works

<!-- #region how-it-works -->
- The routes are registered through Filament's own
  `Resource::registerRoutes()`, inside each resource's route group: slug,
  cluster, parent resource and tenant are handled by Filament.
- The page class of a resource is written on demand to
  `storage/framework/cache`, the way Laravel's real-time facades work, and
  registered with Livewire. It works with `route:cache`.
- The relation page embeds the relation manager as Filament does in the tabs,
  so its state, actions and modals stay in the relation manager.
<!-- #endregion how-it-works -->

## AI agents

<!-- #region ai-agents -->
The package teaches AI coding agents how to use it, so Claude Code, Codex,
Cursor, Copilot and the like write the traits and options correctly:

- **Laravel Boost**: the package ships guidelines and a skill
  (`relation-navigator-development`). Pick `wit3/filament-relation-navigator`
  when you run `php artisan boost:install` (or `boost:update`).
- **llms.txt**: the documentation is also published for agents, as an
  index ([llms.txt](https://wit3.github.io/filament-relation-navigator/llms.txt))
  and as one file
  ([llms-full.txt](https://wit3.github.io/filament-relation-navigator/llms-full.txt)).
  Every page has a Markdown version too: add `.md` to its URL.
- **Without Boost**: point your agent to
  `vendor/wit3/filament-relation-navigator/resources/boost/skills/relation-navigator-development/SKILL.md`,
  for example from your `AGENTS.md` or `CLAUDE.md`.
<!-- #endregion ai-agents -->

## Testing

<!-- #region testing -->
```bash
composer test        # the test suite
composer analyse     # PHPStan, level 8
composer format      # Pint
```

The suite runs on Filament 4 and 5, Laravel 12 and 13, PHP 8.2 to 8.5, and
covers nested resources, clusters, tenancy and SPA mode.

The browser tests (the searchable menu) need Playwright:

```bash
npm install && npx playwright install chromium
vendor/bin/testbench filament:assets
composer test:browser
```
<!-- #endregion testing -->

## Changelog

<!-- #region changelog -->
See [CHANGELOG](https://github.com/wit3/filament-relation-navigator/blob/main/CHANGELOG.md).
<!-- #endregion changelog -->

## Contributing

<!-- #region contributing -->
Issues and pull requests are welcome. Please run `composer test`,
`composer analyse` and `composer format` before opening a pull request.
<!-- #endregion contributing -->

## Security

<!-- #region security -->
Please report security issues privately through
[GitHub security advisories](https://github.com/wit3/filament-relation-navigator/security/advisories/new)
rather than in public issues.
<!-- #endregion security -->

## Credits

<!-- #region credits -->
- [Salvatore Scalzi](https://github.com/wit3)
- [All contributors](https://github.com/wit3/filament-relation-navigator/contributors)
<!-- #endregion credits -->

## License

<!-- #region license -->
The MIT License (MIT). See [LICENSE](https://github.com/wit3/filament-relation-navigator/blob/main/LICENSE.md).
<!-- #endregion license -->
