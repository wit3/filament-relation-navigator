<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\UnrelatedAuthors;

use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\PostsRelationManager;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\UnrelatedAuthors\Pages\ListUnrelatedAuthors;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\UnrelatedAuthors\Pages\ViewUnrelatedAuthor;

/**
 * A resource of the app that does not use the package at all.
 */
class UnrelatedAuthorResource extends Resource
{
    protected static ?string $model = Author::class;

    protected static ?string $slug = 'unrelated-authors';

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
            'index' => ListUnrelatedAuthors::route('/'),
            'view' => ViewUnrelatedAuthor::route('/{record}'),
        ];
    }
}
