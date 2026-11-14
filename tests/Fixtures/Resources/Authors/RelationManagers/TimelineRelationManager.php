<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Wit3\FilamentRelationNavigator\Concerns\InteractsWithRelationPage;

/**
 * Its slug collides with the path of AuthorTimeline.
 */
class TimelineRelationManager extends RelationManager
{
    use InteractsWithRelationPage;

    protected static ?string $relationPageSlug = 'timeline';

    protected static string $relationship = 'comments';

    protected static ?string $title = 'Timeline relation';

    public function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('body')]);
    }
}
