<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class NotesRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $title = 'Secret notes';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('body'),
        ]);
    }
}
