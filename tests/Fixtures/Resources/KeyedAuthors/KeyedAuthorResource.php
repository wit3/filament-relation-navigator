<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\KeyedAuthors;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Wit3\FilamentRelationNavigator\Concerns\HasRelationPages;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\PostsRelationManager;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\KeyedAuthors\Pages\ListKeyedAuthors;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\KeyedAuthors\Pages\ShowKeyedAuthor;

/**
 * Its view page is registered under "details", not "view".
 */
class KeyedAuthorResource extends Resource
{
    use HasRelationPages;

    protected static ?string $model = Author::class;

    protected static ?string $slug = 'keyed-authors';

    protected static ?string $recordTitleAttribute = 'name';

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
        return ['posts' => PostsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKeyedAuthors::route('/'),
            'details' => ShowKeyedAuthor::route('/{record}'),
        ];
    }
}
