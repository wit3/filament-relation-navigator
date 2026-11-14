<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\PlainAuthors;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\PostsRelationManager;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\PublishedCommentsRelationManager;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\PlainAuthors\Pages\ListPlainAuthors;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\PlainAuthors\Pages\PlainAuthorRelationPage;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\PlainAuthors\Pages\ViewPlainAuthor;

/**
 * No record title, and the relations chosen from the resource.
 */
class PlainAuthorResource extends Resource
{
    protected static ?string $model = Author::class;

    protected static ?string $slug = 'plain-authors';

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
            PostsRelationManager::class,
            PublishedCommentsRelationManager::class,
        ];
    }

    public static function hasRelationPage(string $slug): bool
    {
        return $slug !== 'published-comments';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlainAuthors::route('/'),
            'view' => ViewPlainAuthor::route('/{record}'),
            ...PlainAuthorRelationPage::relationRoutes(),
        ];
    }
}
