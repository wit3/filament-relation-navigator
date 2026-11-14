<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\OptInAuthors;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\OptInAuthors\Pages\ListOptInAuthors;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\OptInAuthors\Pages\ViewOptInAuthor;

class OptInAuthorResource extends Resource
{
    protected static ?string $model = Author::class;

    protected static ?string $slug = 'opt-in-authors';

    /**
     * A name from a method, without `$recordTitleAttribute`.
     */
    public static function getRecordTitle(?Model $record): string|Htmlable|null
    {
        return $record instanceof Author ? "Author {$record->name}" : null;
    }

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
            RelationManagers\OptedInCommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOptInAuthors::route('/'),
            'view' => ViewOptInAuthor::route('/{record}'),
        ];
    }
}
