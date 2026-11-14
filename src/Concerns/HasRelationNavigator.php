<?php

namespace Wit3\FilamentRelationNavigator\Concerns;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
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
        return method_exists($this, 'getRecord') && $this->hasRecord() && $this->getRecord()->exists;
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
     * One of the presets in the config (`dropdown`, `searchable` or yours).
     */
    public function getRelationNavigatorPreset(): string
    {
        return (string) config('filament-relation-navigator.menu.preset', 'dropdown');
    }

    public function getRelationNavigatorView(): string
    {
        $preset = $this->getRelationNavigatorPreset();
        $presets = (array) config('filament-relation-navigator.menu.presets', []);

        return $presets[$preset] ?? "filament-relation-navigator::menu.{$preset}";
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
        return RelationNavigator::getItems(
            static::getResource(),
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
        return new HtmlString(view($this->getRelationNavigatorView(), [
            'label' => $label,
            'items' => $items,
            'heading' => __('filament-relation-navigator::navigator.heading'),
            'searchPlaceholder' => __('filament-relation-navigator::navigator.search'),
            'emptyLabel' => __('filament-relation-navigator::navigator.empty'),
            'maxHeight' => (string) config('filament-relation-navigator.menu.max_height', '24rem'),
            'isSearchable' => count($items) >= (int) config('filament-relation-navigator.menu.search_from', 0),
        ])->render());
    }
}
