<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PublishedCommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'publishedComments';

    protected static ?string $title = 'Published comments';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->columns([
                TextColumn::make('body'),
            ]);
    }
}
