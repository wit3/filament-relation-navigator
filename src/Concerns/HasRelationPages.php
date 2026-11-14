<?php

namespace Wit3\FilamentRelationNavigator\Concerns;

/**
 * On a resource: every relation manager in `getRelations()` gets its own
 * page at `/{record}/{slug}`, with no page class to write. A relation
 * manager can still opt out (`hasRelationPage(): false`).
 *
 * To hide the "Open as page" action in the relation managers' tables on the
 * view and edit pages of this resource:
 *
 *     public static function hasRelationPageLinks(): bool
 *     {
 *         return false;
 *     }
 *
 * To skip some relations from the resource:
 *
 *     public static function hasRelationPage(string $slug): bool
 *     {
 *         return $slug !== 'cache-clears';
 *     }
 */
trait HasRelationPages
{
    public static function hasRelationPages(): bool
    {
        return true;
    }
}
