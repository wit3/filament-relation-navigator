<?php

namespace Wit3\FilamentRelationNavigator\Concerns;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Wit3\FilamentRelationNavigator\RelationNavigator;

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

    /**
     * Global search results link to the record's relation pages when the
     * config `global_search_actions` (or hasRelationPageGlobalSearchActions()
     * on the resource) says so. A resource that defines this method itself
     * wins: merge RelationNavigator::getGlobalSearchResultActions() there.
     *
     * @return array<Action>
     */
    public static function getGlobalSearchResultActions(Model $record): array
    {
        return [
            ...parent::getGlobalSearchResultActions($record),
            ...RelationNavigator::getGlobalSearchResultActions(static::class, $record),
        ];
    }
}
