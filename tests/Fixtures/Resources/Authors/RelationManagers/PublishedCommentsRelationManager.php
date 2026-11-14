<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PublishedCommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'publishedComments';

    protected static ?string $title = 'Published comments';

    /**
     * Rendered with the page, so that full-page tests see its content.
     * (`$isLazy` is the name Filament 4.13 knows.)
     */
    protected static bool $isLazy = false;

    /**
     * An author named "No comments" cannot see them: a record that cannot
     * open a page another record can.
     */
    /**
     * Per-page title, subheading and breadcrumb, for an author named
     * "Custom titles" only (null keeps the defaults).
     */
    public static function getRelationPageTitle(Model $ownerRecord): ?string
    {
        return $ownerRecord->getAttribute('name') === 'Custom titles' ? 'Comments by '.$ownerRecord->getAttribute('name') : null;
    }

    public static function getRelationPageSubheading(Model $ownerRecord): ?string
    {
        return $ownerRecord->getAttribute('name') === 'Custom titles' ? 'Only the published ones' : null;
    }

    public static function getRelationPageBreadcrumb(Model $ownerRecord): ?string
    {
        return $ownerRecord->getAttribute('name') === 'Custom titles' ? 'Comments' : null;
    }

    /**
     * A lazy tab badge, for an author named "Lazy badge" only.
     */
    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return $ownerRecord->getAttribute('name') === 'Lazy badge' ? '7' : null;
    }

    public static function isBadgeLazyLoaded(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->getAttribute('name') === 'Lazy badge';
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->getAttribute('name') !== 'No comments';
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->columns([
                TextColumn::make('body'),
            ]);
    }
}
