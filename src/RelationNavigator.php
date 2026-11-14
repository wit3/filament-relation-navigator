<?php

namespace Wit3\FilamentRelationNavigator;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\RelationManagers\RelationManagerConfiguration;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Wit3\FilamentRelationNavigator\Concerns\HasRelationNavigator;
use Wit3\FilamentRelationNavigator\Concerns\HasRelationPages;
use Wit3\FilamentRelationNavigator\Pages\RelationPage;

final class RelationNavigator
{
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
            $page = self::getGeneratedRelationPageClass($resource);

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

                if ($page = self::getGeneratedRelationPageClass($resource)) {
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

            if (! $pageClass::canAccess(['record' => $record])) {
                continue;
            }

            $items[] = new RelationNavigatorItem(
                key: $pageKey,
                label: (string) $pageClass::getNavigationLabel(),
                url: $resource::getUrl($pageKey, ['record' => $record], shouldGuessMissingParameters: true),
                icon: $pageClass::getNavigationIcon(),
                isActive: $activePageKey === $pageKey,
            );
        }

        $relationPage = self::getRelationPageClass($resource);

        if (! $relationPage) {
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
        if (! method_exists($page, 'getRecord')) {
            return [];
        }

        $items = self::getItems(
            $page::getResource(),
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
