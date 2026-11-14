<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\AuthorHistory;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\AuthorRelationPage;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\AuthorStats;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\AuthorTimeline;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\EditAuthor;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\ListAuthors;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\ViewAuthor;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\GroupOnlyCommentsRelationManager;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\HiddenPageRelationManager;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\NotesRelationManager;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\PostsRelationManager;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\PublishedCommentsRelationManager;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\TimelineRelationManager;

class AuthorResource extends Resource
{
    protected static ?string $model = Author::class;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Switched by the tests of the resource-level options.
     *
     * @var array<string, mixed>
     */
    public static array $options = [];

    public static function hasRelationPageLinks(): bool
    {
        return static::$options['hasRelationPageLinks'] ?? true;
    }

    public static function configureRelationPageAction(Action $action): Action
    {
        return isset(static::$options['relationPageAction']) ? (static::$options['relationPageAction'])($action) : $action;
    }

    public static function canAccessRelationPage(Model $record, string $slug): bool
    {
        return isset(static::$options['canAccessRelationPage']) ? (static::$options['canAccessRelationPage'])($record, $slug) : true;
    }

    public static function hasRelationPageGlobalSearchActions(): bool
    {
        return static::$options['hasRelationPageGlobalSearchActions'] ?? (bool) config('filament-relation-navigator.global_search_actions', false);
    }

    /**
     * Resource-level options of the menu: the option, else the config.
     */
    public static function getRelationNavigatorPreset(): string
    {
        return static::$options['preset'] ?? config('filament-relation-navigator.menu.preset', 'github');
    }

    public static function hasRelationNavigatorRecordNavigation(): bool
    {
        return static::$options['recordNavigation'] ?? (bool) config('filament-relation-navigator.record_navigation', false);
    }

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
            TextColumn::make('name')->sortable()->searchable(),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            'posts' => PostsRelationManager::class,
            PublishedCommentsRelationManager::class,
            'notes' => NotesRelationManager::class,
            HiddenPageRelationManager::class,
            TimelineRelationManager::class,
            'activity' => RelationGroup::make('Activity', [
                PostsRelationManager::class,
                PublishedCommentsRelationManager::class,
                GroupOnlyCommentsRelationManager::class,
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
            'authorTimeline' => AuthorTimeline::route('/{record}/timeline'),
            ...AuthorRelationPage::relationRoutes(),
        ];
    }
}
