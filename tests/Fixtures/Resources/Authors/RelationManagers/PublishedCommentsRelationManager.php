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
     * An author named "No comments" cannot see them: a record that cannot
     * open a page another record can.
     */
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
