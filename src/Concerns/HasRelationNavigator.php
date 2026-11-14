<?php

namespace Wit3\FilamentRelationNavigator\Concerns;

use Filament\Resources\Resource;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use InvalidArgumentException;
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
        return (bool) config('filament-relation-navigator.menu.badges', true);
    }

    /**
     * One of the presets in the config (`simple`, `github` or yours), or a
     * view name.
     */
    public function getRelationNavigatorPreset(): string
    {
        return (string) config('filament-relation-navigator.menu.preset', 'simple');
    }

    /**
     * Opens the menu from anywhere on the page, outside text fields (config
     * `menu.shortcut`): 'alt+shift+r', 'mod+shift+p'... 'mod' is ⌘ on a Mac
     * and Ctrl elsewhere. Null turns it off; a shortcut of the panel's
     * global search is ignored.
     */
    public function getRelationNavigatorShortcut(): ?string
    {
        $shortcut = config('filament-relation-navigator.menu.shortcut', 'alt+shift+r');

        if (! is_string($shortcut) || blank($shortcut)) {
            return null;
        }

        $shortcut = strtolower(str_replace(' ', '', $shortcut));

        // Never take a shortcut that Filament's global search uses.
        return RelationNavigator::clashesWithFilament($shortcut) ? null : $shortcut;
    }

    /**
     * A search field on top of the list, whatever the preset.
     */
    public function isRelationNavigatorSearchable(): bool
    {
        return (bool) config('filament-relation-navigator.menu.searchable', false);
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
            $this->hasRelationNavigatorBadges(),
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
        return (bool) config('filament-relation-navigator.menu.mobile', true);
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
            'shortcut' => $shortcut = $this->getRelationNavigatorShortcut(),
            'shortcutKeys' => RelationNavigator::getShortcutKeys($shortcut),
        ];
    }
}
