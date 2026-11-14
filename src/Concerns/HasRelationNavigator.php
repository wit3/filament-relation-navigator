<?php

namespace Wit3\FilamentRelationNavigator\Concerns;

use Filament\Resources\Resource;
use Filament\Support\Facades\FilamentView;
use Filament\Support\View\ComponentAttributeBag;
use Filament\Support\View\Components\BadgeComponent;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use InvalidArgumentException;
use Livewire\Attributes\Renderless;
use Wit3\FilamentRelationNavigator\Pages\RelationPage;
use Wit3\FilamentRelationNavigator\RelationNavigator;
use Wit3\FilamentRelationNavigator\RelationNavigatorItem;

/**
 * Turns the last breadcrumb of a record page into a menu with the record's
 * pages and its relation pages, and puts the page itself in that menu.
 *
 * Add it to every record page you want in the menu: view, edit or a custom
 * page. If the page already overrides `getBreadcrumbs()`, pass your array
 * through `withRelationNavigator()`:
 *
 *     public function getBreadcrumbs(): array
 *     {
 *         return $this->withRelationNavigator([...]);
 *     }
 *
 * A custom page that uses `InteractsWithRecord` itself has two
 * `getBreadcrumbs()`: tell PHP which one wins.
 *
 *     use HasRelationNavigator, InteractsWithRecord {
 *         HasRelationNavigator::getBreadcrumbs insteadof InteractsWithRecord;
 *         InteractsWithRecord::getBreadcrumbs as getRecordBreadcrumbs;
 *     }
 *
 * Every option (preset, search, badges, shortcut, small screens, previous
 * and next record, tab redirects) is read from the page, then from a static
 * method with the same name on the resource, then from the config:
 *
 *     // On the resource: no search on any of its pages.
 *     public static function isRelationNavigatorSearchable(): bool
 *     {
 *         return false;
 *     }
 */
trait HasRelationNavigator
{
    /**
     * The trait needs a page with a record: ViewRecord, EditRecord or a page
     * using InteractsWithRecord already has these. A page without a record
     * fails when its class is loaded.
     */
    abstract public function getRecord(): Model;

    abstract public function hasRecord(): bool;

    /**
     * Livewire runs this after the page's own mount(): with config
     * `redirect_relation_tabs`, a link to a relation manager's tab
     * (`?relation=posts`) opens its relation page instead.
     */
    public function mountHasRelationNavigator(): void
    {
        if (! $this->shouldRedirectRelationNavigatorTabs()) {
            return;
        }

        $url = $this->getRelationNavigatorTabRedirectUrl(request()->query('relation'));

        if ($url !== null) {
            $this->redirect($url, navigate: FilamentView::hasSpaMode($url));
        }
    }

    /**
     * Off by default (config `redirect_relation_tabs`).
     */
    public function shouldRedirectRelationNavigatorTabs(): bool
    {
        return (bool) $this->resolveRelationNavigatorOption(__FUNCTION__, fn (): mixed => config('filament-relation-navigator.redirect_relation_tabs', false));
    }

    /**
     * The relation page of a relation manager's tab key, as in `?relation=`.
     */
    public function getRelationNavigatorTabRedirectUrl(mixed $relation): ?string
    {
        if ($this instanceof RelationPage || ! is_scalar($relation) || blank($relation) || ! $this->hasRelationNavigator()) {
            return null;
        }

        $resource = static::getResource();
        $relationPage = is_subclass_of($resource, Resource::class) ? RelationNavigator::getRelationPageClass($resource) : null;

        if (! $relationPage) {
            return null;
        }

        foreach ($relationPage::getRelationPageDefinitions() as $slug => $definition) {
            if ((string) $definition->key !== (string) $relation) {
                continue;
            }

            $pageKey = $relationPage::getPageKey((string) $slug);

            if (! RelationNavigator::hasRoute($resource, $pageKey)
                || ! $definition->canViewForRecord($this->getRecord(), $relationPage::getContextPageClass())) {
                return null;
            }

            return $resource::getUrl($pageKey, ['record' => $this->getRecord()], shouldGuessMissingParameters: true);
        }

        return null;
    }

    /**
     * @return array<string | int, string | Htmlable>
     */
    public function getBreadcrumbs(): array
    {
        // A custom page that uses InteractsWithRecord itself aliases its
        // breadcrumbs as getRecordBreadcrumbs() (see the class docblock).
        $breadcrumbs = method_exists($this, 'getRecordBreadcrumbs')
            ? $this->getRecordBreadcrumbs()
            : parent::getBreadcrumbs();

        return $this->withRelationNavigator($breadcrumbs);
    }

    /**
     * @param  array<string | int, string | Htmlable>  $breadcrumbs
     * @return array<string | int, string | Htmlable>
     */
    public function withRelationNavigator(array $breadcrumbs): array
    {
        if (! $this->hasRelationNavigator()) {
            return $breadcrumbs;
        }

        $lastKey = array_key_last($breadcrumbs);

        if ($lastKey === null) {
            return $breadcrumbs;
        }

        $items = $this->getRelationNavigatorItems();

        if (count($items) < 2) {
            return $breadcrumbs;
        }

        $label = $breadcrumbs[$lastKey];
        unset($breadcrumbs[$lastKey]);

        $breadcrumbs[] = $this->renderRelationNavigator($label, $items);

        return $breadcrumbs;
    }

    public function hasRelationNavigator(): bool
    {
        return $this->hasRecord() && $this->getRecord()->exists;
    }

    /**
     * Relation managers' badges in the menu (config `menu.badges`). They run
     * the same queries as the tab badges: turn them off if they are expensive.
     */
    public function hasRelationNavigatorBadges(): bool
    {
        return (bool) $this->resolveRelationNavigatorOption(__FUNCTION__, fn (): mixed => config('filament-relation-navigator.menu.badges', true));
    }

    /**
     * Badges loaded when the menu opens instead of with the page (config
     * `menu.badges` = 'lazy'): the page renders without their queries.
     */
    public function hasDeferredRelationNavigatorBadges(): bool
    {
        return (bool) $this->resolveRelationNavigatorOption(__FUNCTION__, fn (): bool => config('filament-relation-navigator.menu.badges', true) === 'lazy');
    }

    /**
     * The badges of the menu, keyed by page key, for the deferred badges.
     *
     * @return array<string, array{badge: string, class: string, style: string}>
     */
    #[Renderless]
    public function getRelationNavigatorBadges(): array
    {
        $resource = static::getResource();

        if (! $this->hasRelationNavigator() || ! $this->hasRelationNavigatorBadges() || ! is_subclass_of($resource, Resource::class)) {
            return [];
        }

        $badges = [];

        foreach (RelationNavigator::getItems($resource, $this->getRecord(), withBadges: true) as $item) {
            if (blank($item->badge)) {
                continue;
            }

            $attributes = (new ComponentAttributeBag)->color(BadgeComponent::class, $item->badgeColor ?? 'primary');

            $badges[$item->key] = [
                'badge' => (string) $item->badge,
                'class' => (string) $attributes->get('class', ''),
                'style' => (string) $attributes->get('style', ''),
            ];
        }

        return $badges;
    }

    /**
     * One of the presets in the config (`github`, the default, `simple` or
     * yours), or a view name.
     */
    public function getRelationNavigatorPreset(): string
    {
        return (string) $this->resolveRelationNavigatorOption(__FUNCTION__, fn (): mixed => config('filament-relation-navigator.menu.preset', 'github'));
    }

    /**
     * Opens the menu from anywhere on the page, outside text fields (config
     * `menu.shortcut`): 'alt+shift+r', 'mod+shift+p'... 'mod' is ⌘ on a Mac
     * and Ctrl elsewhere. Null turns it off; a shortcut of the panel's
     * global search is ignored.
     */
    public function getRelationNavigatorShortcut(): ?string
    {
        $shortcut = $this->resolveRelationNavigatorOption(__FUNCTION__, fn (): mixed => config('filament-relation-navigator.menu.shortcut', 'alt+shift+r'));

        if (! is_string($shortcut) || blank($shortcut)) {
            return null;
        }

        $shortcut = strtolower(str_replace(' ', '', $shortcut));

        // Never take a shortcut that Filament's global search uses.
        return RelationNavigator::clashesWithFilament($shortcut) ? null : $shortcut;
    }

    /**
     * A search field on top of the list, whatever the preset (on by default).
     */
    public function isRelationNavigatorSearchable(): bool
    {
        return (bool) $this->resolveRelationNavigatorOption(__FUNCTION__, fn (): mixed => config('filament-relation-navigator.menu.searchable', true));
    }

    /**
     * Buttons to the previous and next record, on the same page (config
     * `record_navigation`, off by default: it adds a few queries per render).
     */
    public function hasRelationNavigatorRecordNavigation(): bool
    {
        return (bool) $this->resolveRelationNavigatorOption(__FUNCTION__, fn (): mixed => config('filament-relation-navigator.record_navigation', false));
    }

    /**
     * The records that previous and next walk through, ordered by key. By
     * default the resource's own query (tenant and your scopes included);
     * null for nested resources, which are not supported yet.
     *
     * @return Builder<Model> | null
     */
    public function getRelationNavigatorRecordNavigationQuery(): ?Builder
    {
        $resource = static::getResource();

        if (! is_subclass_of($resource, Resource::class) || $resource::getParentResourceRegistration()) {
            return null;
        }

        return $resource::getRecordRouteBindingEloquentQuery();
    }

    /**
     * The previous and next record the user can view, each on the same page
     * when it can open it, otherwise on its first page in the menu.
     *
     * @return array{previous: RelationNavigatorItem | null, next: RelationNavigatorItem | null}
     */
    public function getRelationNavigatorRecordNeighbours(): array
    {
        $neighbours = ['previous' => null, 'next' => null];

        if (! $this->hasRelationNavigator() || ! $this->hasRelationNavigatorRecordNavigation()) {
            return $neighbours;
        }

        $query = $this->getRelationNavigatorRecordNavigationQuery();

        if (! $query) {
            return $neighbours;
        }

        $record = $this->getRecord();
        $keyName = $record->getQualifiedKeyName();

        $candidates = [
            'previous' => (clone $query)->reorder()->where($keyName, '<', $record->getKey())->orderByDesc($keyName),
            'next' => (clone $query)->reorder()->where($keyName, '>', $record->getKey())->orderBy($keyName),
        ];

        $resource = static::getResource();

        if (! is_subclass_of($resource, Resource::class)) {
            return $neighbours;
        }

        foreach ($candidates as $direction => $candidateQuery) {
            // The nearest record the user can view: a few at most, so that a
            // run of hidden records does not turn into many queries.
            foreach ($candidateQuery->limit(5)->get() as $neighbour) {
                if (! $resource::canView($neighbour)) {
                    continue;
                }

                $items = RelationNavigator::getItems($resource, $neighbour, $this->getRelationNavigatorActivePageKey(), withBadges: false);
                $item = collect($items)->firstWhere('isActive', true) ?? ($items[0] ?? null);

                if (! $item) {
                    continue;
                }

                $title = $resource::getRecordTitle($neighbour);

                $neighbours[$direction] = new RelationNavigatorItem(
                    key: $item->key,
                    label: $title instanceof Htmlable ? $title->toHtml() : (string) $title,
                    url: $item->url,
                );

                break;
            }
        }

        return $neighbours;
    }

    public function renderRelationNavigatorRecordNavigation(): ?Htmlable
    {
        $neighbours = $this->getRelationNavigatorRecordNeighbours();

        if (! $neighbours['previous'] && ! $neighbours['next']) {
            return null;
        }

        return new HtmlString(app(ViewFactory::class)->make(
            $this->ensureRelationNavigatorView('filament-relation-navigator::record-navigation'),
            [
                'previous' => $neighbours['previous'],
                'next' => $neighbours['next'],
                'previousLabel' => __('filament-relation-navigator::navigator.previous_record'),
                'nextLabel' => __('filament-relation-navigator::navigator.next_record'),
            ],
        )->render());
    }

    /**
     * Every option of the menu has three levels: this page (override the
     * method), its resource (a static method with the same name, for all
     * its pages) and the config (for all resources).
     */
    private function resolveRelationNavigatorOption(string $method, \Closure $fromConfig): mixed
    {
        $resource = static::getResource();

        if (method_exists($resource, $method)) {
            return $resource::$method();
        }

        return $fromConfig();
    }

    /**
     * The menu view, checked: it comes from the config or from a page
     * override, so a typo gets a clear message.
     *
     * @return view-string
     */
    private function resolveRelationNavigatorView(): string
    {
        return $this->ensureRelationNavigatorView($this->getRelationNavigatorView());
    }

    /**
     * @return view-string
     */
    private function ensureRelationNavigatorView(string $view): string
    {
        if (! app(ViewFactory::class)->exists($view)) {
            throw new InvalidArgumentException("The breadcrumb menu view [{$view}] does not exist. Check `filament-relation-navigator.menu.preset` and `menu.presets`, or getRelationNavigatorPreset() on [".static::class.'].');
        }

        return $view;
    }

    public function getRelationNavigatorView(): string
    {
        $preset = $this->getRelationNavigatorPreset();
        $presets = (array) config('filament-relation-navigator.menu.presets', []);

        if (array_key_exists($preset, $presets)) {
            return $presets[$preset];
        }

        // A view name works as a preset too.
        return view()->exists($preset) ? $preset : "filament-relation-navigator::menu.{$preset}";
    }

    public function getRelationNavigatorActivePageKey(): ?string
    {
        return RelationNavigator::getActivePageKey($this);
    }

    /**
     * @return array<RelationNavigatorItem>
     */
    public function getRelationNavigatorItems(): array
    {
        $resource = static::getResource();

        if (! is_subclass_of($resource, Resource::class)) {
            return [];
        }

        return RelationNavigator::getItems(
            $resource,
            $this->getRecord(),
            $this->getRelationNavigatorActivePageKey(),
            $this->hasRelationNavigatorBadges() && ! $this->hasDeferredRelationNavigatorBadges(),
        );
    }

    /**
     * @param  array<RelationNavigatorItem>  $items
     */
    protected function renderRelationNavigator(string|Htmlable $label, array $items): Htmlable
    {
        return new HtmlString(app(ViewFactory::class)->make(
            $this->resolveRelationNavigatorView(),
            $this->getRelationNavigatorViewData($label, $items),
        )->render());
    }

    /**
     * On small screens Filament hides the breadcrumbs: this selector shows
     * above the heading instead (config `menu.mobile`). Rendered by the
     * package through a render hook.
     */
    public function hasRelationNavigatorMobileMenu(): bool
    {
        return (bool) $this->resolveRelationNavigatorOption(__FUNCTION__, fn (): mixed => config('filament-relation-navigator.menu.mobile', true));
    }

    public function renderRelationNavigatorMobileMenu(): ?Htmlable
    {
        if (! $this->hasRelationNavigatorMobileMenu() || ! $this->hasRelationNavigator()) {
            return null;
        }

        $items = $this->getRelationNavigatorItems();

        if (count($items) < 2) {
            return null;
        }

        $active = collect($items)->firstWhere('isActive', true);

        return new HtmlString(app(ViewFactory::class)->make(
            $this->ensureRelationNavigatorView('filament-relation-navigator::menu.mobile'),
            $this->getRelationNavigatorViewData($active->label ?? $this->getBreadcrumb() ?? $this->getTitle(), $items),
        )->render());
    }

    /**
     * @param  array<RelationNavigatorItem>  $items
     * @return array<string, mixed>
     */
    private function getRelationNavigatorViewData(string|Htmlable $label, array $items): array
    {
        return [
            'label' => $label,
            'items' => $items,
            'heading' => __('filament-relation-navigator::navigator.heading'),
            'searchPlaceholder' => __('filament-relation-navigator::navigator.search'),
            'emptyLabel' => __('filament-relation-navigator::navigator.empty'),
            'maxHeight' => (string) config('filament-relation-navigator.menu.max_height', '24rem'),
            'isSearchable' => $this->isRelationNavigatorSearchable()
                && (count($items) >= (int) config('filament-relation-navigator.menu.search_from', 0)),
            'hasSeparator' => (bool) config('filament-relation-navigator.menu.separator', true),
            'hasDeferredBadges' => $this->hasRelationNavigatorBadges() && $this->hasDeferredRelationNavigatorBadges(),
            'shortcut' => $shortcut = $this->getRelationNavigatorShortcut(),
            'shortcutKeys' => RelationNavigator::getShortcutKeys($shortcut),
        ];
    }
}
