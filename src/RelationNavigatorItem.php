<?php

namespace Wit3\FilamentRelationNavigator;

use Wit3\FilamentRelationNavigator\Pages\RelationPage;

/**
 * One link of the breadcrumb menu: the record's own pages (view, edit) and
 * one per relation page.
 */
final class RelationNavigatorItem
{
    /**
     * @param  string | array<string> | null  $badgeColor
     */
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly string $url,
        public readonly mixed $icon = null,
        public readonly ?string $badge = null,
        public readonly string|array|null $badgeColor = null,
        public readonly bool $isActive = false,
    ) {}

    /**
     * A relation page, as opposed to a page of the record (view, edit...).
     */
    public function isRelationPage(): bool
    {
        return str_starts_with($this->key, RelationPage::PAGE_KEY_PREFIX);
    }
}
