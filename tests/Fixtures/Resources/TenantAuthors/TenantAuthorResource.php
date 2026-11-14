<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\TenantAuthors;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Wit3\FilamentRelationNavigator\Concerns\HasRelationPages;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\TenantAuthors\Pages\ListTenantAuthors;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\TenantAuthors\Pages\ViewTenantAuthor;

class TenantAuthorResource extends Resource
{
    use HasRelationPages;

    protected static ?string $model = Author::class;

    protected static ?string $slug = 'tenant-authors';

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
            'posts' => RelationManagers\PostsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTenantAuthors::route('/'),
            'view' => ViewTenantAuthor::route('/{record}'),
        ];
    }
}
