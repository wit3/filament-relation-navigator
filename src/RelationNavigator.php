<?php

namespace Wit3\FilamentRelationNavigator;

use Closure;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\RelationManagers\RelationManagerConfiguration;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Wit3\FilamentRelationNavigator\Concerns\HasRelationNavigator;
use Wit3\FilamentRelationNavigator\Concerns\HasRelationPages;
use Wit3\FilamentRelationNavigator\Pages\RelationPage;

final class RelationNavigator
{
    /**
     * The name of the "Open as page" action in a relation manager's table.
     */
    public const RELATION_PAGE_ACTION = 'relationNavigatorOpenPage';

    /**
     * @internal Package plumbing: not covered by semantic versioning.
     *
     * A page of the record is in the menu when it uses
     * {@see HasRelationNavigator}: view, edit or any custom record page.
     * Relation pages are listed separately, one per relation.
     */
    public static function isRecordPageInMenu(string $pageClass): bool
    {
        if (is_subclass_of($pageClass, RelationPage::class)) {
            return false;
        }

        $traits = class_uses_recursive($pageClass);

        return in_array(HasRelationNavigator::class, $traits, true)
            && in_array(InteractsWithRecord::class, $traits, true);
    }

    /**
     * The record check that Filament's view and edit pages run when they
     * mount (`view` and `update` policies), which their canAccess() leaves
     * out. Other pages check the record themselves: true.
     *
     * @param  class-string<Page>  $pageClass
     */
    public static function canAccessRecordPage(string $pageClass, Model $record): bool
    {
        $resource = $pageClass::getResource();

        return match (true) {
            is_subclass_of($pageClass, ViewRecord::class) => $resource::canView($record),
            is_subclass_of($pageClass, EditRecord::class) => $resource::canEdit($record),
            default => true,
        };
    }

    /**
     * @internal Package plumbing: not covered by semantic versioning.
     *
     * @param  class-string<RelationManager> | RelationGroup | RelationManagerConfiguration  $manager
     * @return class-string<RelationManager> | null
     */
    public static function normalizeManagerClass(string|RelationGroup|RelationManagerConfiguration $manager): ?string
    {
        return match (true) {
            $manager instanceof RelationGroup => null,
            $manager instanceof RelationManagerConfiguration => $manager->relationManager,
            default => $manager,
        };
    }

    /**
     * The relation page of a resource: the class you registered in
     * `getPages()`, otherwise the generated one when the resource or one of
     * its relation managers opts in.
     *
     * @param  class-string<resource>  $resource
     * @return class-string<RelationPage> | null
     */
    public static function getRelationPageClass(string $resource): ?string
    {
        return self::remember("relation-page|{$resource}", fn (): ?string => self::getRegisteredRelationPageClass($resource) ?? self::getGeneratedRelationPageClass($resource));
    }

    private const CACHE_KEY = 'filament-relation-navigator.cache';

    /**
     * @internal A value worked out once per request (per panel, resource
     * configuration and locale): pages, relations and resources do not
     * change during a request. Kept on the request, so it never outlives it
     * (Octane).
     *
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public static function remember(string $key, Closure $callback): mixed
    {
        $attributes = request()->attributes;
        $cache = $attributes->get(self::CACHE_KEY, []);
        $key = implode('|', [$key, Filament::getCurrentPanel()?->getId(), Filament::getCurrentResourceConfigurationKey(), app()->getLocale()]);

        if (! array_key_exists($key, $cache)) {
            $cache[$key] = $callback();
            $attributes->set(self::CACHE_KEY, $cache + $attributes->get(self::CACHE_KEY, []));
        }

        return $cache[$key];
    }

    /**
     * @internal For tests that change pages or relations within a request.
     */
    public static function flushCache(): void
    {
        request()->attributes->remove(self::CACHE_KEY);
    }

    /**
     * @internal Package plumbing: not covered by semantic versioning.
     *
     * @param  class-string<resource>  $resource
     * @return class-string<RelationPage> | null
     */
    public static function getRegisteredRelationPageClass(string $resource): ?string
    {
        foreach ($resource::getPages() as $registration) {
            $page = $registration->getPage();

            if (is_subclass_of($page, RelationPage::class)) {
                return $page;
            }
        }

        return null;
    }

    /**
     * @internal Package plumbing: not covered by semantic versioning.
     *
     * @param  class-string<resource>  $resource
     * @return class-string<RelationPage> | null
     */
    public static function getGeneratedRelationPageClass(string $resource): ?string
    {
        if (! self::optsIn($resource) || self::getRegisteredRelationPageClass($resource)) {
            return null;
        }

        $page = GeneratedRelationPages::classFor($resource);

        // Autoloads (and writes) the generated class.
        if (! is_subclass_of($page, RelationPage::class)) {
            return null;
        }

        return filled($page::getRelationPageDefinitions()) ? $page : null;
    }

    /**
     * @internal Package plumbing: not covered by semantic versioning.
     *
     * Cheap check before any class is generated: the resource uses
     * {@see HasRelationPages}, or one of its relation managers has
     * `hasRelationPage(): true`.
     *
     * @param  class-string<resource>  $resource
     */
    public static function optsIn(string $resource): bool
    {
        if (method_exists($resource, 'hasRelationPages') && $resource::hasRelationPages()) {
            return true;
        }

        foreach ($resource::getRelations() as $manager) {
            $managers = ($manager instanceof RelationGroup) ? $manager->getManagers() : [$manager];

            foreach ($managers as $groupedManager) {
                $managerClass = self::normalizeManagerClass($groupedManager);

                if ($managerClass && method_exists($managerClass, 'hasRelationPage') && $managerClass::hasRelationPage()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @internal Package plumbing: not covered by semantic versioning.
     *
     * Routes of the generated relation pages, inside each resource's own
     * route group (slug, cluster, parent resource, tenant).
     */
    public static function registerRoutes(Panel $panel): void
    {
        foreach ($panel->getResources() as $resource) {
            $page = is_subclass_of($resource, Resource::class) ? self::getGeneratedRelationPageClass($resource) : null;

            if (! $page) {
                continue;
            }

            $registrations = $page::relationRoutes();

            $resource::registerRoutes($panel, function () use ($panel, $registrations): void {
                foreach ($registrations as $name => $registration) {
                    $registration->registerRoute($panel)?->name($name);
                }
            });
        }

        // A resource registered several times with configurations
        // (`UserResource::make('admins')`): one set of routes each, under the
        // configuration's slug, as Filament does for the resource's pages.
        foreach ($panel->getResourceConfigurations() as $configuration) {
            $resource = $configuration->getResource();

            if (! is_subclass_of($resource, Resource::class)) {
                continue;
            }

            Filament::setCurrentResourceConfigurationKey($configuration->getKey());

            try {
                $page = self::getGeneratedRelationPageClass($resource);

                if (! $page) {
                    continue;
                }

                $registrations = $page::relationRoutes();
                $key = $configuration->getKey();

                $resource::registerRoutes($panel, function () use ($panel, $registrations, $key): void {
                    foreach ($registrations as $name => $registration) {
                        $registration->registerRoute($panel)
                            ?->middleware("resource-configuration:{$key}")
                            ->name($name);
                    }
                }, $configuration);
            } finally {
                Filament::setCurrentResourceConfigurationKey(null);
            }
        }
    }

    /**
     * @internal Package plumbing: not covered by semantic versioning.
     *
     * Livewire must know the generated pages on every request, cached
     * routes included.
     */
    public static function registerLivewireComponents(): void
    {
        $registered = [];

        foreach (Filament::getPanels() as $panel) {
            // Resources registered only through a configuration count too.
            $resources = [
                ...$panel->getResources(),
                ...array_map(fn ($configuration): string => $configuration->getResource(), $panel->getResourceConfigurations()),
            ];

            foreach ($resources as $resource) {
                if (isset($registered[$resource])) {
                    continue;
                }

                $registered[$resource] = true;

                if (is_subclass_of($resource, Resource::class) && ($page = self::getGeneratedRelationPageClass($resource))) {
                    Livewire::component(self::getLivewireComponentName($page), $page);
                }
            }
        }
    }

    /**
     * @internal Package plumbing: not covered by semantic versioning.
     */
    public static function getLivewireComponentName(string $page): string
    {
        return collect(explode('\\', $page))
            ->map(fn (string $segment): string => Str::kebab($segment))
            ->implode('.');
    }

    /**
     * Every page of the record the user can open: the record pages with
     * {@see HasRelationNavigator} in the order of `getPages()`, then one per
     * relation page in the order of `getRelations()`.
     *
     * @param  class-string<resource>  $resource
     * @return array<RelationNavigatorItem>
     */
    public static function getItems(string $resource, Model $record, ?string $activePageKey = null, bool $withBadges = true, bool $resolveDeferredBadges = false): array
    {
        $items = [];
        $pages = $resource::getPages();

        $seenPageClasses = [];

        foreach ($pages as $pageKey => $registration) {
            /** @var class-string<Page> $pageClass */
            $pageClass = $registration->getPage();

            if (isset($seenPageClasses[$pageClass]) || (! self::isRecordPageInMenu($pageClass))) {
                continue;
            }

            $seenPageClasses[$pageClass] = true;

            if (! $pageClass::canAccess(['record' => $record]) || ! self::canAccessRecordPage($pageClass, $record)) {
                continue;
            }

            $label = $pageClass::getNavigationLabel();

            $items[] = new RelationNavigatorItem(
                key: $pageKey,
                label: $label instanceof Htmlable ? $label->toHtml() : $label,
                url: $resource::getUrl($pageKey, ['record' => $record], shouldGuessMissingParameters: true),
                icon: $pageClass::getNavigationIcon(),
                isActive: $activePageKey === $pageKey,
            );
        }

        $relationPage = self::getRelationPageClass($resource);

        if (! $relationPage) {
            return $items;
        }

        // The relation pages need the record's own page first (as
        // RelationPage::canAccess() does): a record the user cannot view has
        // none, even if its relation managers would show.
        if (! $relationPage::canAccess(['record' => $record])) {
            return $items;
        }

        $contextPageClass = $relationPage::getContextPageClass();

        foreach ($relationPage::getRelationPageDefinitions() as $slug => $definition) {
            $pageKey = $relationPage::getPageKey($slug);

            // The cheap check first: no route, no authorization to run.
            if (! self::hasRoute($resource, $pageKey) || ! $definition->canViewForRecord($record, $contextPageClass)) {
                continue;
            }

            // A relation manager can leave its badge out of the menu.
            $managerClass = $definition->getManagerClass();
            $withBadge = $withBadges && ! ($managerClass && method_exists($managerClass, 'hasRelationNavigatorBadge') && ! $managerClass::hasRelationNavigatorBadge());

            // A lazy tab badge is loaded when the menu opens, unless asked now.
            $isBadgeDeferred = $withBadge && (! $resolveDeferredBadges) && $definition->isBadgeLazy($record, $contextPageClass);
            $hasBadge = $withBadge && ! $isBadgeDeferred;

            $items[] = new RelationNavigatorItem(
                key: $pageKey,
                label: $definition->getTitle($record, $contextPageClass),
                url: $resource::getUrl($pageKey, ['record' => $record], shouldGuessMissingParameters: true),
                icon: $definition->getIcon($record, $contextPageClass),
                badge: $hasBadge ? $definition->getBadge($record, $contextPageClass) : null,
                badgeColor: $hasBadge ? $definition->getBadgeColor($record, $contextPageClass) : null,
                isActive: $activePageKey === $pageKey,
                isBadgeDeferred: $isBadgeDeferred,
            );
        }

        return $items;
    }

    /**
     * Native record sub-navigation built from the same items. Use it from
     * the resource:
     *
     *     public static function getRecordSubNavigation(Page $page): array
     *     {
     *         return RelationNavigator::getRecordSubNavigation($page);
     *     }
     *
     * @return array<NavigationItem>
     */
    public static function getRecordSubNavigation(Page $page): array
    {
        $resource = $page::getResource();

        if (! method_exists($page, 'getRecord') || ! is_subclass_of($resource, Resource::class)) {
            return [];
        }

        // Rendered with the page: no deferred badges here.
        $items = self::getItems(
            $resource,
            $page->getRecord(),
            self::getActivePageKey($page),
            resolveDeferredBadges: true,
        );

        return array_map(
            fn (RelationNavigatorItem $item): NavigationItem => NavigationItem::make($item->label)
                ->icon($item->icon)
                ->url($item->url)
                ->badge($item->badge, $item->badgeColor)
                ->isActiveWhen(fn (): bool => $item->isActive),
            $items,
        );
    }

    /**
     * @internal Package plumbing: not covered by semantic versioning.
     *
     * The current panel may not have the route (a resource in two panels).
     *
     * @param  class-string<resource>  $resource
     */
    public static function hasRoute(string $resource, string $pageKey): bool
    {
        return Route::has($resource::getRouteBaseName(Filament::getCurrentOrDefaultPanel()).'.'.$pageKey);
    }

    /**
     * @internal Package plumbing: not covered by semantic versioning.
     *
     * The small-screen menu of the page being rendered, if it uses
     * HasRelationNavigator.
     */
    public static function renderMobileMenu(): ?Htmlable
    {
        $page = Livewire::current();

        if (! $page instanceof Page || ! in_array(HasRelationNavigator::class, class_uses_recursive($page), true)) {
            return null;
        }

        $menu = method_exists($page, 'renderRelationNavigatorMobileMenu') ? $page->renderRelationNavigatorMobileMenu() : null;

        return $menu instanceof Htmlable ? $menu : null;
    }

    /**
     * @internal Package plumbing: not covered by semantic versioning.
     *
     * The previous and next record buttons of the page being rendered, if
     * it uses HasRelationNavigator and turned them on.
     */
    public static function renderRecordNavigation(): ?Htmlable
    {
        $page = Livewire::current();

        if (! $page instanceof Page || ! in_array(HasRelationNavigator::class, class_uses_recursive($page), true)) {
            return null;
        }

        $buttons = method_exists($page, 'renderRelationNavigatorRecordNavigation') ? $page->renderRelationNavigatorRecordNavigation() : null;

        return $buttons instanceof Htmlable ? $buttons : null;
    }

    /**
     * @var array<string, bool>
     */
    private static array $viewsWithDeferredBadges = [];

    /**
     * @internal Package plumbing: not covered by semantic versioning.
     *
     * Whether menu views can load deferred badges: they call the page's
     * getRelationNavigatorBadges(), themselves or through the package's list
     * partial. Views published before 1.3, or presets of your own that render
     * the badges themselves, cannot: their badges then come with the page.
     */
    public static function supportsDeferredBadges(string ...$views): bool
    {
        $partialPath = self::viewPath(self::LIST_PARTIAL);

        foreach ($views as $view) {
            // By file: a published view is another file.
            $key = self::viewPath($view).'|'.$partialPath;

            if (! (self::$viewsWithDeferredBadges[$key] ??= self::viewLoadsDeferredBadges($view))) {
                return false;
            }
        }

        return true;
    }

    private const LIST_PARTIAL = 'filament-relation-navigator::menu.partials.list';

    private static function viewPath(string $view): string
    {
        try {
            return app(ViewFactory::class)->getFinder()->find($view);
        } catch (\InvalidArgumentException) {
            return '';
        }
    }

    private static function viewLoadsDeferredBadges(string $view): bool
    {
        // The package's own presets, unpublished: no need to read them.
        $packageViews = realpath(__DIR__.'/../resources/views');
        $paths = [self::viewPath($view), self::viewPath(self::LIST_PARTIAL)];

        if ($packageViews !== false && collect($paths)->every(fn (string $path): bool => str_starts_with((string) realpath($path), $packageViews))) {
            return true;
        }

        $content = self::viewSource($view);

        if ($content === null) {
            return false;
        }

        if (str_contains($content, 'getRelationNavigatorBadges')) {
            return true;
        }

        return str_contains($content, 'menu.partials.list')
            && str_contains(self::viewSource(self::LIST_PARTIAL) ?? '', 'getRelationNavigatorBadges');
    }

    private static function viewSource(string $view): ?string
    {
        $path = self::viewPath($view);

        if ($path === '') {
            return null;
        }

        $content = @file_get_contents($path);

        return is_string($content) ? $content : null;
    }

    /**
     * @internal Package plumbing: not covered by semantic versioning.
     *
     * The most records a list remembers for previous and next.
     */
    public const REMEMBERED_LIST_SIZE = 100;

    /**
     * @internal Package plumbing: not covered by semantic versioning.
     *
     * ...and at most this many bytes of keys, comma-separated.
     */
    public const REMEMBERED_LIST_BYTES = 1024;

    /**
     * @internal Package plumbing: not covered by semantic versioning.
     *
     * The most lists remembered at once, the oldest dropped first.
     */
    public const REMEMBERED_LISTS = 5;

    private const REMEMBERED_LISTS_SESSION_KEY = 'filament-relation-navigator.lists';

    /**
     * @internal Package plumbing: not covered by semantic versioning.
     *
     * Remembers the records a resource's list shows, in its order (sort,
     * filters, search, tab, grouping), for previous and next on the record
     * pages. The list is the resource's list page or, for a nested resource,
     * the parent's relation manager table that lists it (its related
     * resource). Called by the package when the table renders; the records
     * are the ones the table has already loaded.
     */
    public static function rememberListRecords(ListRecords|RelationManager $list): void
    {
        $resource = ($list instanceof RelationManager) ? $list::getRelatedResource() : $list::getResource();

        // A table that defers loading has not run its query yet: never force it.
        if ($resource === null || ! is_subclass_of($resource, Resource::class) || ! self::listFeedsRecordNavigation($resource) || ! $list->isTableLoaded()) {
            return;
        }

        // A relation manager table only for a nested resource: it renders on
        // the parent's pages, where a list of its own would replace the one
        // the user follows. Never with the cookie session driver, which keeps
        // one list only: the parent's.
        if (($list instanceof RelationManager) && ((! $resource::getParentResourceRegistration()) || (config('session.driver') === 'cookie'))) {
            return;
        }

        $records = $list->getTableRecords();
        $records = ($records instanceof Collection) ? $records : collect($records->items());

        // At most 100 keys in about 1 KB, as one comma-separated string:
        // sessions kept in a cookie have 4 KB for everything.
        $joined = '';
        $count = 0;

        foreach ($records as $record) {
            if (! $record instanceof Model) {
                continue;
            }

            $key = (string) $record->getKey();

            // A key with a comma cannot be stored this way: walk by key.
            if (str_contains($key, ',')) {
                return;
            }

            $candidate = ($joined === '') ? $key : "{$joined},{$key}";

            if (($count >= self::REMEMBERED_LIST_SIZE) || (strlen($candidate) > self::REMEMBERED_LIST_BYTES)) {
                break;
            }

            $joined = $candidate;
            $count++;
        }

        // One session entry for every list, the most recent five (one with
        // the cookie session driver).
        $lists = session()->get(self::REMEMBERED_LISTS_SESSION_KEY, []);
        $lists = is_array($lists) ? $lists : [];
        $listKey = self::rememberedListKey($resource);

        unset($lists[$listKey]);
        $lists[$listKey] = $joined;

        $keep = (config('session.driver') === 'cookie') ? 1 : self::REMEMBERED_LISTS;

        session()->put(self::REMEMBERED_LISTS_SESSION_KEY, array_slice($lists, -$keep, preserve_keys: true));
    }

    /**
     * @internal Package plumbing: not covered by semantic versioning.
     *
     * The keys of the records the resource's list last showed this user.
     *
     * @param  class-string<resource>  $resource
     * @return list<string> | null
     */
    public static function getRememberedListKeys(string $resource): ?array
    {
        $lists = session()->get(self::REMEMBERED_LISTS_SESSION_KEY, []);
        $keys = is_array($lists) ? ($lists[self::rememberedListKey($resource)] ?? null) : null;

        return match (true) {
            is_string($keys) => ($keys === '') ? [] : explode(',', $keys),
            // Stored as an array by 1.6.0.
            is_array($keys) => array_values(array_map('strval', $keys)),
            default => null,
        };
    }

    /**
     * On the list page there is no record page to ask: the resource or the
     * config decide.
     *
     * @param  class-string<resource>  $resource
     */
    private static function listFeedsRecordNavigation(string $resource): bool
    {
        $navigation = method_exists($resource, 'hasRelationNavigatorRecordNavigation')
            ? (bool) $resource::hasRelationNavigatorRecordNavigation()
            : (bool) config('filament-relation-navigator.record_navigation', false);

        $followsList = method_exists($resource, 'shouldRelationNavigatorRecordNavigationFollowList')
            ? (bool) $resource::shouldRelationNavigatorRecordNavigationFollowList()
            : (bool) config('filament-relation-navigator.record_navigation_follows_list', true);

        return $navigation && $followsList;
    }

    /**
     * One list per panel, tenant, resource and resource configuration.
     *
     * @param  class-string<resource>  $resource
     */
    private static function rememberedListKey(string $resource): string
    {
        return md5(implode('|', [
            Filament::getCurrentOrDefaultPanel()?->getId(),
            Filament::getTenant()?->getKey(),
            $resource,
            Filament::getCurrentResourceConfigurationKey(),
        ]));
    }

    /**
     * Links to the record's relation pages under a global search result
     * (config `global_search_actions`: false, true or the most links to
     * show). Resources with HasRelationPages get them by themselves; a
     * resource that defines getGlobalSearchResultActions() merges them in:
     *
     *     public static function getGlobalSearchResultActions(Model $record): array
     *     {
     *         return [...RelationNavigator::getGlobalSearchResultActions(static::class, $record)];
     *     }
     *
     * @param  class-string<resource>  $resource
     * @return array<Action>
     */
    public static function getGlobalSearchResultActions(string $resource, Model $record): array
    {
        // Global search calls this for every result: leave early. Nested
        // resources are left out: their URLs need the parent of each result.
        $limit = self::getGlobalSearchActionsLimit($resource);

        if ($limit === 0 || $resource::getParentResourceRegistration()) {
            return [];
        }

        $relationPage = self::getRelationPageClass($resource);

        if (! $relationPage || ! $relationPage::canAccess(['record' => $record])) {
            return [];
        }

        $contextPageClass = $relationPage::getContextPageClass();
        $actions = [];

        foreach ($relationPage::getRelationPageDefinitions() as $slug => $definition) {
            if (count($actions) >= $limit) {
                break;
            }

            $managerClass = $definition->getManagerClass();

            if ($managerClass && method_exists($managerClass, 'hasRelationPageGlobalSearchAction') && ! $managerClass::hasRelationPageGlobalSearchAction()) {
                continue;
            }

            $pageKey = $relationPage::getPageKey((string) $slug);

            if (! self::hasRoute($resource, $pageKey) || ! $definition->canViewForRecord($record, $contextPageClass)) {
                continue;
            }

            $actions[] = Action::make('relationNavigator'.Str::studly((string) $slug))
                ->label($definition->getTitle($record, $contextPageClass))
                ->url($resource::getUrl($pageKey, ['record' => $record], shouldGuessMissingParameters: true));
        }

        return $actions;
    }

    /**
     * How many relation links a global search result gets: 0 when off. The
     * resource can turn them on or off with hasRelationPageGlobalSearchActions().
     *
     * @param  class-string<resource>  $resource
     */
    private static function getGlobalSearchActionsLimit(string $resource): int
    {
        $config = config('filament-relation-navigator.global_search_actions', false);

        $enabled = method_exists($resource, 'hasRelationPageGlobalSearchActions')
            ? (bool) $resource::hasRelationPageGlobalSearchActions()
            : ($config !== false && $config !== null && $config !== 0);

        if (! $enabled) {
            return 0;
        }

        return (is_int($config) && $config > 0) ? $config : PHP_INT_MAX;
    }

    /**
     * The "Open as page" action in the header of a relation manager's table,
     * on the view and edit pages. Added by the package to every relation
     * manager that has its own page; see {@see self::hasRelationPageAction()}.
     */
    public static function addRelationPageAction(RelationManager $manager): void
    {
        if (! self::hasRelationPageAction($manager)) {
            return;
        }

        // A lazy relation manager mounts without its table: it gets the
        // action on the request that loads it.
        // Filament's own property: never fail a render if it is renamed.
        try {
            $table = new \ReflectionProperty($manager, 'table');
        } catch (\ReflectionException) {
            return;
        }

        if (! $table->isInitialized($manager)) {
            return;
        }

        $table = $manager->getTable();

        foreach ($table->getHeaderActions() as $action) {
            if ($action instanceof Action && $action->getName() === self::RELATION_PAGE_ACTION) {
                return;
            }
        }

        $url = false;
        $resolveUrl = function () use ($manager, &$url): ?string {
            if ($url === false) {
                $url = self::getRelationManagerPageUrl($manager);

                // Never a link to the page it is on (its own relation page).
                $url = ($url !== null && self::isOriginalUrl($url)) ? null : $url;
            }

            return $url;
        };

        // First, so that the relation manager's own actions keep the end.
        $table->headerActions([
            self::makeRelationPageAction($manager)
                ->url($resolveUrl)
                ->visible(fn (): bool => $resolveUrl() !== null),
            ...$table->getHeaderActions(),
        ]);
    }

    /**
     * The container key of the callbacks of configureRelationPageActionUsing():
     * one list per application, so they never pile up across apps or tests.
     */
    private const ACTION_CONFIGURATIONS = 'filament-relation-navigator.relation-page-action-configurations';

    /**
     * Changes the "Open as page" action of every relation manager, from a
     * service provider. The callback receives the action (already set up from
     * the config) and the relation manager; it changes the action in place
     * or returns a new one.
     *
     *     RelationNavigator::configureRelationPageActionUsing(
     *         fn (Action $action) => $action->button()->label('Full page'),
     *     );
     *
     * @param  Closure(Action, RelationManager): mixed  $callback
     */
    public static function configureRelationPageActionUsing(Closure $callback): void
    {
        self::relationPageActionConfigurations()->append($callback);
    }

    /**
     * @internal Package plumbing: not covered by semantic versioning.
     */
    public static function flushRelationPageActionConfigurations(): void
    {
        app()->forgetInstance(self::ACTION_CONFIGURATIONS);
    }

    /**
     * @return \ArrayObject<int, Closure(Action, RelationManager): mixed>
     */
    private static function relationPageActionConfigurations(): \ArrayObject
    {
        if (! app()->bound(self::ACTION_CONFIGURATIONS)) {
            app()->instance(self::ACTION_CONFIGURATIONS, new \ArrayObject);
        }

        return app(self::ACTION_CONFIGURATIONS);
    }

    /**
     * The "Open as page" action, without its URL: from the config
     * (`relation_manager_action`), then the callbacks of
     * configureRelationPageActionUsing(), then `configureRelationPageAction()`
     * on the resource and on the relation manager.
     */
    public static function makeRelationPageAction(RelationManager $manager): Action
    {
        $options = (array) config('filament-relation-navigator.relation_manager_action', []);

        $label = filled($options['label'] ?? null)
            ? (string) __((string) $options['label'])
            : (string) __('filament-relation-navigator::navigator.open_as_page');

        $action = Action::make(self::RELATION_PAGE_ACTION)
            ->label($label)
            ->color($options['color'] ?? 'gray');

        // The same icon as the shipped config.
        $icon = array_key_exists('icon', $options) ? $options['icon'] : 'heroicon-m-arrow-up-right';

        if (filled($icon)) {
            $action
                ->icon($icon)
                ->iconPosition(($options['icon_position'] ?? 'after') === 'before' ? IconPosition::Before : IconPosition::After);
        }

        match ($options['style'] ?? 'link') {
            'button' => $action->button(),
            'icon_button', 'icon-button', 'icon' => $action->iconButton()->tooltip($label),
            default => $action->link(),
        };

        if (filled($options['size'] ?? null)) {
            $action->size($options['size']);
        }

        if ($options['new_tab'] ?? false) {
            $action->openUrlInNewTab();
        }

        foreach (self::relationPageActionConfigurations() as $configure) {
            $result = $configure($action, $manager);
            $action = ($result instanceof Action) ? $result : $action;
        }

        // The resource (static), then the relation manager: called on the
        // instance, so a static or an instance method both work.
        $pageClass = $manager->getPageClass();
        $resource = is_subclass_of($pageClass, Page::class) ? $pageClass::getResource() : null;
        $configureOnResource = is_string($resource) ? [$resource, 'configureRelationPageAction'] : null;

        if ($configureOnResource !== null && method_exists($resource, 'configureRelationPageAction') && is_callable($configureOnResource)) {
            $result = $configureOnResource($action, $manager);
            $action = ($result instanceof Action) ? $result : $action;
        }

        if (method_exists($manager, 'configureRelationPageAction')) {
            $result = $manager->configureRelationPageAction($action, $manager);
            $action = ($result instanceof Action) ? $result : $action;
        }

        return $action;
    }

    /**
     * The action shows unless it is turned off in the config
     * (`relation_manager_link`), on the resource (`hasRelationPageLinks()`)
     * or on the relation manager (`hasRelationPageLink()`).
     */
    public static function hasRelationPageAction(RelationManager $manager): bool
    {
        if (! config('filament-relation-navigator.relation_manager_link', true)) {
            return false;
        }

        $managerClass = $manager::class;

        if (method_exists($managerClass, 'hasRelationPageLink') && ! $managerClass::hasRelationPageLink()) {
            return false;
        }

        $pageClass = $manager->getPageClass();

        if (! is_subclass_of($pageClass, Page::class)) {
            return false;
        }

        $resource = $pageClass::getResource();

        return ! (method_exists($resource, 'hasRelationPageLinks') && ! $resource::hasRelationPageLinks());
    }

    /**
     * @deprecated 1.4.0 The link is now an action in the header of the
     *     relation manager's table, added by the package. This still renders
     *     the link of 1.3.0, for a render hook of your own.
     */
    public static function renderRelationManagerLink(): ?Htmlable
    {
        if (! config('filament-relation-navigator.relation_manager_link', true)) {
            return null;
        }

        $manager = Livewire::current();

        if (! $manager instanceof RelationManager) {
            return null;
        }

        $url = self::getRelationManagerPageUrl($manager);

        // A relation page embeds the relation manager with the view page as
        // its page class: look at the page it is on, also on Livewire
        // requests (lazy loading, table actions).
        if ($url === null || self::isOriginalUrl($url)) {
            return null;
        }

        return new HtmlString(app(ViewFactory::class)->make(self::view('relation-manager-link'), [
            'url' => $url,
            'label' => __('filament-relation-navigator::navigator.open_as_page'),
        ])->render());
    }

    /**
     * Whether the URL is the page the current Livewire components are on,
     * also on Livewire requests (lazy loading, table actions).
     */
    private static function isOriginalUrl(string $url): bool
    {
        $normalize = fn (string $url): string => rtrim((string) strtok($url, '?#'), '/');

        return $normalize($url) === $normalize((string) Livewire::originalUrl());
    }

    /**
     * A view of the package, checked: a published copy may be missing.
     *
     * @return view-string
     */
    private static function view(string $name): string
    {
        $view = "filament-relation-navigator::{$name}";

        if (! app(ViewFactory::class)->exists($view)) {
            throw new \InvalidArgumentException("The view [{$view}] does not exist.");
        }

        return $view;
    }

    /**
     * The URL of the relation page that shows this relation manager (or the
     * group it belongs to), for its owner record. Null on the relation page
     * itself, or when the relation manager has no page.
     */
    public static function getRelationManagerPageUrl(RelationManager $manager): ?string
    {
        $pageClass = $manager->getPageClass();

        if (! is_subclass_of($pageClass, Page::class) || is_subclass_of($pageClass, RelationPage::class)) {
            return null;
        }

        $resource = $pageClass::getResource();

        if (! is_subclass_of($resource, Resource::class)) {
            return null;
        }

        $relationPage = self::getRelationPageClass($resource);

        if (! $relationPage) {
            return null;
        }

        $ownerRecord = $manager->getOwnerRecord();

        // Never a link the user cannot open.
        if (! $relationPage::canAccess(['record' => $ownerRecord])) {
            return null;
        }

        $slug = self::findRelationPageSlug($relationPage, $resource, $manager, $ownerRecord);

        if ($slug === null) {
            return null;
        }

        $pageKey = $relationPage::getPageKey($slug);

        return $resource::getUrl($pageKey, ['record' => $ownerRecord], shouldGuessMissingParameters: true);
    }

    /**
     * The slug of the relation page of a relation manager instance. The same
     * class can be listed twice with different properties
     * (`PostsRelationManager::make([...])`): the entry whose properties the
     * instance has wins, then the plain class.
     *
     * @param  class-string<RelationPage>  $relationPage
     * @param  class-string<resource>  $resource
     */
    private static function findRelationPageSlug(string $relationPage, string $resource, RelationManager $manager, Model $ownerRecord): ?string
    {
        $contextPageClass = $relationPage::getContextPageClass();
        $definitions = $relationPage::getRelationPageDefinitions();

        // The entries this relation manager matches: one whose properties the
        // instance has first, then its own page before the page of a group it
        // belongs to, then the first one. Matching needs no authorization.
        $candidates = [];

        foreach ($definitions as $slug => $definition) {
            $entries = ($definition->manager instanceof RelationGroup) ? $definition->manager->getManagers() : [$definition->manager];

            foreach ($entries as $entry) {
                if (self::normalizeManagerClass($entry) !== $manager::class) {
                    continue;
                }

                $properties = ($entry instanceof RelationManagerConfiguration) ? $entry->getProperties() : [];
                $matches = ($properties !== []) && collect($properties)->every(
                    fn (mixed $value, string $property): bool => property_exists($manager, $property) && self::sameValue($manager->{$property}, $value),
                );

                if (($properties !== []) && ! $matches) {
                    continue;
                }

                $candidates[(string) $slug] = max($candidates[(string) $slug] ?? -1, ($matches ? 2 : 0) + ($definition->isGroup() ? 0 : 1));
            }
        }

        // Stable: equal scores keep the order of getRelations().
        uksort($candidates, fn (string $a, string $b): int => ($candidates[$b] <=> $candidates[$a]) ?: (array_search($a, array_keys($definitions), true) <=> array_search($b, array_keys($definitions), true)));

        // The best page the user can open, as the menu would list it.
        foreach (array_keys($candidates) as $slug) {
            if (self::hasRoute($resource, $relationPage::getPageKey($slug)) && $definitions[$slug]->canViewForRecord($ownerRecord, $contextPageClass)) {
                return $slug;
            }
        }

        return null;
    }

    /**
     * A property of a configured relation manager, as Livewire hydrates it:
     * models by key, other objects by value.
     */
    private static function sameValue(mixed $actual, mixed $expected): bool
    {
        return match (true) {
            $expected instanceof Model => $actual instanceof Model && $actual->is($expected),
            is_object($expected) => $actual == $expected,
            default => $actual === $expected,
        };
    }

    /**
     * A shortcut as the menu uses it: lowercase, without spaces, and null
     * when it cannot be matched (an unsupported key) or when the panel's
     * global search uses it.
     */
    public static function normalizeShortcut(?string $shortcut): ?string
    {
        if (blank($shortcut)) {
            return null;
        }

        $shortcut = strtolower(str_replace(' ', '', $shortcut));

        if (self::getShortcutCondition($shortcut) === null || self::clashesWithFilament($shortcut)) {
            return null;
        }

        return $shortcut;
    }

    /**
     * The keys of a Mousetrap shortcut as shown to people, on a Mac and
     * elsewhere: 'alt+shift+r' gives ⌥ ⇧ R and Alt Shift R.
     *
     * @return array{mac: array<string>, other: array<string>}
     */
    public static function getShortcutKeys(?string $shortcut): array
    {
        $keys = ['mac' => [], 'other' => []];

        if (blank($shortcut)) {
            return $keys;
        }

        $names = [
            'mod' => ['⌘', 'Ctrl'],
            'command' => ['⌘', 'Win'],
            'cmd' => ['⌘', 'Win'],
            'meta' => ['⌘', 'Win'],
            'ctrl' => ['⌃', 'Ctrl'],
            'control' => ['⌃', 'Ctrl'],
            'alt' => ['⌥', 'Alt'],
            'option' => ['⌥', 'Alt'],
            'shift' => ['⇧', 'Shift'],
        ];

        foreach (explode('+', strtolower($shortcut)) as $key) {
            [$mac, $other] = $names[$key] ?? [strtoupper($key), strtoupper($key)];

            $keys['mac'][] = $mac;
            $keys['other'][] = $other;
        }

        return $keys;
    }

    /**
     * The JavaScript condition, for a `keydown.window` listener, that is true
     * when the shortcut is pressed outside text fields. It compares the
     * physical key (`event.code`), so it works with any keyboard layout and
     * with Option on a Mac.
     */
    public static function getShortcutCondition(string $shortcut): ?string
    {
        $modifiers = ['alt' => false, 'shift' => false, 'ctrl' => false, 'meta' => false, 'mod' => false];
        $code = null;

        foreach (explode('+', strtolower($shortcut)) as $key) {
            $modifier = match ($key) {
                'alt', 'option' => 'alt',
                'shift' => 'shift',
                'ctrl', 'control' => 'ctrl',
                'meta', 'command', 'cmd' => 'meta',
                'mod' => 'mod',
                default => null,
            };

            if ($modifier !== null) {
                $modifiers[$modifier] = true;

                continue;
            }

            $code = match (true) {
                (bool) preg_match('/^[a-z]$/', $key) => 'Key'.strtoupper($key),
                (bool) preg_match('/^[0-9]$/', $key) => 'Digit'.$key,
                default => ['/' => 'Slash', '.' => 'Period', ',' => 'Comma', ';' => 'Semicolon', 'enter' => 'Enter', 'space' => 'Space'][$key] ?? null,
            };
        }

        if ($code === null) {
            return null;
        }

        $isMac = '/Mac|iPhone|iPad/.test(navigator.userAgent)';
        $ctrl = $modifiers['mod'] ? "({$isMac} ? \$event.ctrlKey === ".json_encode($modifiers['ctrl']).' : $event.ctrlKey === true)' : '$event.ctrlKey === '.json_encode($modifiers['ctrl']);
        $meta = $modifiers['mod'] ? "({$isMac} ? \$event.metaKey === true : \$event.metaKey === ".json_encode($modifiers['meta']).')' : '$event.metaKey === '.json_encode($modifiers['meta']);

        return implode(' && ', [
            '! $event.repeat',
            '! $event.target.closest(\'input, textarea, select, [contenteditable]:not([contenteditable=false])\')',
            '$event.code === '.json_encode($code),
            '$event.altKey === '.json_encode($modifiers['alt']),
            '$event.shiftKey === '.json_encode($modifiers['shift']),
            $ctrl,
            $meta,
        ]);
    }

    /**
     * Whether the shortcut is one of the panel's global search shortcuts.
     */
    public static function clashesWithFilament(string $shortcut, ?Panel $panel = null): bool
    {
        $panel ??= Filament::getCurrentOrDefaultPanel();

        if (! $panel) {
            return false;
        }

        $normalize = fn (string $binding): array => collect(explode('+', strtolower($binding)))
            ->map(fn (string $key): string => match ($key) {
                'option' => 'alt',
                'control' => 'ctrl',
                'command', 'cmd' => 'meta',
                default => $key,
            })
            ->sort()
            ->values()
            ->all();

        // 'mod' is ⌘ on a Mac and Ctrl elsewhere: compare both.
        $candidates = str_contains($shortcut, 'mod')
            ? [$normalize(str_replace('mod', 'meta', $shortcut)), $normalize(str_replace('mod', 'ctrl', $shortcut))]
            : [$normalize($shortcut)];

        foreach ($panel->getGlobalSearchKeyBindings() as $binding) {
            $bindings = str_contains($binding, 'mod')
                ? [$normalize(str_replace('mod', 'meta', $binding)), $normalize(str_replace('mod', 'ctrl', $binding))]
                : [$normalize($binding)];

            foreach ($bindings as $bound) {
                if (in_array($bound, $candidates, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @internal Package plumbing: not covered by semantic versioning.
     */
    public static function getActivePageKey(Page $page): ?string
    {
        if ($page instanceof RelationPage) {
            return $page->getRelationPageKey();
        }

        try {
            return $page::getResourcePageName();
        } catch (\LogicException) {
            return null;
        }
    }
}
