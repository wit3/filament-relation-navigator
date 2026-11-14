<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Posts\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RevisionsRelationManager extends RelationManager
{
    protected static string $relationship = 'revisions';

    protected static ?string $title = 'Revisions';

    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('note')->columns([
            TextColumn::make('note'),
        ]);
    }
}
