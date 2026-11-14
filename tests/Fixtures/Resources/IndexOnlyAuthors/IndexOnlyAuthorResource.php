<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\IndexOnlyAuthors;

use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Wit3\FilamentRelationNavigator\Concerns\HasRelationPages;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\PostsRelationManager;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\IndexOnlyAuthors\Pages\ListIndexOnlyAuthors;

/**
 * A list page and relation pages, no view or edit page.
 */
class IndexOnlyAuthorResource extends Resource
{
    use HasRelationPages;

    protected static ?string $model = Author::class;

    protected static ?string $slug = 'index-only-authors';

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')]);
    }

    public static function getRelations(): array
    {
        return ['posts' => PostsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIndexOnlyAuthors::route('/'),
        ];
    }
}
