<?php

namespace Wit3\FilamentRelationNavigator\Concerns;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Wit3\FilamentRelationNavigator\RelationNavigator;
use Wit3\FilamentRelationNavigator\RelationNavigatorItem;

/**
 * Turns the last breadcrumb of a record page ("View", "Edit", the relation
 * page) into a menu with the record's other pages and its relation pages.
 *
 * Add it to the resource's view / edit page. If the page already overrides
 * `getBreadcrumbs()`, pass your array through `withRelationNavigator()`:
 *
 *     public function getBreadcrumbs(): array
 *     {
 *         return $this->withRelationNavigator([...]);
 *     }
 */
trait HasRelationNavigator
{
    /**
     * @return array<string | int, string | Htmlable>
     */
    public function getBreadcrumbs(): array
    {
        return $this->withRelationNavigator(parent::getBreadcrumbs());
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
     * Relation managers' badges in the menu. They run the same queries as
     * the tab badges: turn them off if they are expensive.
     */
    public function hasRelationNavigatorBadges(): bool
    {
        return true;
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
        return new HtmlString(view('filament-relation-navigator::breadcrumb-menu', [
            'label' => $label,
            'items' => $items,
        ])->render());
    }
}
