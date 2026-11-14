<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors;

use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\AuthorHistory;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\AuthorRelationPage;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\AuthorStats;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\EditAuthor;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\ListAuthors;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\ViewAuthor;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\HiddenPageRelationManager;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\NotesRelationManager;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\PostsRelationManager;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\PublishedCommentsRelationManager;

class AuthorResource extends Resource
{
    protected static ?string $model = Author::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name'),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name'),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            'posts' => PostsRelationManager::class,
            PublishedCommentsRelationManager::class,
            'notes' => NotesRelationManager::class,
            HiddenPageRelationManager::class,
            'activity' => RelationGroup::make('Activity', [
                PostsRelationManager::class,
                PublishedCommentsRelationManager::class,
            ]),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuthors::route('/'),
            'view' => ViewAuthor::route('/{record}'),
            'edit' => EditAuthor::route('/{record}/edit'),
            'stats' => AuthorStats::route('/{record}/stats'),
            'history' => AuthorHistory::route('/{record}/history'),
            ...AuthorRelationPage::relationRoutes(),
        ];
    }
}
