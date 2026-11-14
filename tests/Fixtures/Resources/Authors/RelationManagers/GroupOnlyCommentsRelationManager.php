<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers;

/**
 * Listed only inside the "Activity" group: its only page is the group's.
 * Rendered with the page (not lazy), like PublishedCommentsRelationManager.
 */
class GroupOnlyCommentsRelationManager extends PublishedCommentsRelationManager
{
    protected static ?string $title = 'Group only comments';
}
