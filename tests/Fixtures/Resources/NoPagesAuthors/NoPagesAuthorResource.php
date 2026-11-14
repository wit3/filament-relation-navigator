<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\NoPagesAuthors;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\NoPagesAuthors\Pages\ListNoPagesAuthors;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\NoPagesAuthors\Pages\ViewNoPagesAuthor;

class NoPagesAuthorResource extends Resource
{
    protected static ?string $model = Author::class;

    protected static ?string $slug = 'no-pages-authors';

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PostsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNoPagesAuthors::route('/'),
            'view' => ViewNoPagesAuthor::route('/{record}'),
        ];
    }
}
