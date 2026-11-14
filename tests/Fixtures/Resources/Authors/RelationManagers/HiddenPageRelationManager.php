<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Wit3\FilamentRelationNavigator\Concerns\InteractsWithRelationPage;

class HiddenPageRelationManager extends RelationManager
{
    use InteractsWithRelationPage;

    protected static bool $hasRelationPage = false;

    protected static string $relationship = 'comments';

    protected static ?string $title = 'All comments';

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('body'),
        ]);
    }
}
