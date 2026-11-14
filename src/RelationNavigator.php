<?php

namespace Wit3\FilamentRelationNavigator;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\RelationManagers\RelationManagerConfiguration;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
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
        return self::getRegisteredRelationPageClass($resource) ?? self::getGeneratedRelationPageClass($resource);
    }

    /**
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
     * Livewire must know the generated pages on every request, cached
     * routes included.
     */
    public static function registerLivewireComponents(): void
    {
        $registered = [];

        foreach (Filament::getPanels() as $panel) {
            foreach ($panel->getResources() as $resource) {
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
    public static function getItems(string $resource, Model $record, ?string $activePageKey = null, bool $withBadges = true): array
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
            if (! $definition->canViewForRecord($record, $contextPageClass)) {
                continue;
            }

            $pageKey = $relationPage::getPageKey($slug);

            if (! self::hasRoute($resource, $pageKey)) {
                continue;
            }

            $items[] = new RelationNavigatorItem(
                key: $pageKey,
                label: $definition->getTitle($record, $contextPageClass),
                url: $resource::getUrl($pageKey, ['record' => $record], shouldGuessMissingParameters: true),
                icon: $definition->getIcon($record, $contextPageClass),
                badge: $withBadges ? $definition->getBadge($record, $contextPageClass) : null,
                badgeColor: $withBadges ? $definition->getBadgeColor($record, $contextPageClass) : null,
                isActive: $activePageKey === $pageKey,
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

        $items = self::getItems(
            $resource,
            $page->getRecord(),
            self::getActivePageKey($page),
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
     * The current panel may not have the route (a resource in two panels).
     *
     * @param  class-string<resource>  $resource
     */
    public static function hasRoute(string $resource, string $pageKey): bool
    {
        return Route::has($resource::getRouteBaseName(Filament::getCurrentOrDefaultPanel()).'.'.$pageKey);
    }

    /**
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
        $table = new \ReflectionProperty($manager, 'table');

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
                $url = self::isOnRelationPage() ? null : self::getRelationManagerPageUrl($manager);
            }

            return $url;
        };

        // First, so that the relation manager's own actions keep the end.
        $table->headerActions([
            Action::make(self::RELATION_PAGE_ACTION)
                ->label(__('filament-relation-navigator::navigator.open_as_page'))
                ->icon(Heroicon::ArrowUpRight)
                ->iconPosition(IconPosition::After)
                ->link()
                ->color('gray')
                ->url($resolveUrl)
                ->visible(fn (): bool => $resolveUrl() !== null),
            ...$table->getHeaderActions(),
        ]);
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
        if ($url === null || self::isOnRelationPage()) {
            return null;
        }

        return new HtmlString(app(ViewFactory::class)->make(self::view('relation-manager-link'), [
            'url' => $url,
            'label' => __('filament-relation-navigator::navigator.open_as_page'),
        ])->render());
    }

    /**
     * Whether the current Livewire components are on a relation page.
     */
    private static function isOnRelationPage(): bool
    {
        try {
            $route = Route::getRoutes()->match(Request::create((string) Livewire::originalUrl()));
        } catch (\Throwable) {
            return false;
        }

        return array_key_exists(RelationPage::ROUTE_PARAMETER, $route->defaults);
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
        $slug = self::findRelationPageSlug($relationPage, $manager, $ownerRecord);

        if ($slug === null) {
            return null;
        }

        $pageKey = $relationPage::getPageKey($slug);

        if (! self::hasRoute($resource, $pageKey)) {
            return null;
        }

        return $resource::getUrl($pageKey, ['record' => $ownerRecord], shouldGuessMissingParameters: true);
    }

    /**
     * The slug of the relation page of a relation manager instance. The same
     * class can be listed twice with different properties
     * (`PostsRelationManager::make([...])`): the entry whose properties the
     * instance has wins, then the plain class.
     *
     * @param  class-string<RelationPage>  $relationPage
     */
    private static function findRelationPageSlug(string $relationPage, RelationManager $manager, Model $ownerRecord): ?string
    {
        $contextPageClass = $relationPage::getContextPageClass();
        $fallback = null;

        foreach ($relationPage::getRelationPageDefinitions() as $slug => $definition) {
            $entries = $definition->isGroup()
                ? $definition->getGroup($ownerRecord, $contextPageClass)->getManagers()
                : [$definition->manager];

            foreach ($entries as $entry) {
                if (self::normalizeManagerClass($entry) !== $manager::class) {
                    continue;
                }

                $properties = ($entry instanceof RelationManagerConfiguration) ? $entry->getProperties() : [];

                if ($properties === []) {
                    $fallback ??= (string) $slug;

                    continue;
                }

                $matches = collect($properties)->every(
                    fn (mixed $value, string $property): bool => property_exists($manager, $property) && $manager->{$property} === $value,
                );

                if ($matches) {
                    return (string) $slug;
                }
            }
        }

        return $fallback;
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
