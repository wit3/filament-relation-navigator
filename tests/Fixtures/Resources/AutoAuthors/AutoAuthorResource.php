<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\AutoAuthors;

use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Wit3\FilamentRelationNavigator\Concerns\HasRelationPages;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\AutoAuthors\Pages\ListAutoAuthors;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\AutoAuthors\Pages\ViewAutoAuthor;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Widgets\AuthorStats;

class AutoAuthorResource extends Resource
{
    use HasRelationPages;

    protected static ?string $model = Author::class;

    protected static ?string $slug = 'auto-authors';

    protected static ?string $recordTitleAttribute = 'name';

    public static function relationPageHeader(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('name')->label('Header author'),
        ]);
    }

    /** @return array<Action> */
    public static function getRelationPageHeaderActions(): array
    {
        return [
            Action::make('greet')
                ->label(fn (Author $record): string => "Greet {$record->name}")
                ->action(fn (Author $record) => $record->update(['name' => "{$record->name} (greeted)"])),
        ];
    }

    /** @return array<class-string> */
    public static function getRelationPageHeaderWidgets(): array
    {
        return [AuthorStats::class];
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
            'posts' => RelationManagers\PostsRelationManager::class,
            'notes' => RelationManagers\NotesRelationManager::class,
            'featured-posts' => RelationManagers\PostsRelationManager::make(['note' => 'Only the featured ones']),
            RelationManagers\HiddenPageRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAutoAuthors::route('/'),
            'view' => ViewAutoAuthor::route('/{record}'),
        ];
    }
}
