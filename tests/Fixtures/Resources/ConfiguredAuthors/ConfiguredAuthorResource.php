<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\ConfiguredAuthors;

use Filament\Resources\Resource;
use Filament\Resources\ResourceConfiguration;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Wit3\FilamentRelationNavigator\Concerns\HasRelationPages;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\PostsRelationManager;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\ConfiguredAuthors\Pages\ListConfiguredAuthors;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\ConfiguredAuthors\Pages\ViewConfiguredAuthor;

/**
 * Registered only through a configuration: ConfiguredAuthorResource::make('vip').
 */
class ConfiguredAuthorResource extends Resource
{
    use HasRelationPages;

    protected static ?string $model = Author::class;

    protected static ?string $configurationClass = ResourceConfiguration::class;

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
        return [
            'posts' => PostsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConfiguredAuthors::route('/'),
            'view' => ViewConfiguredAuthor::route('/{record}'),
        ];
    }
}
