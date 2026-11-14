<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers;

use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Components\Attributes\Unexposed;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;

class PostsRelationManager extends RelationManager
{
    protected static string $relationship = 'posts';

    protected static ?string $title = 'Posts';

    protected static string|\BackedEnum|null $icon = Heroicon::OutlinedDocumentText;

    /**
     * Switched by the global search tests.
     */
    public static bool $globalSearchAction = true;

    /**
     * Switched by the tests of previous and next: rows that open PostResource.
     *
     * @var class-string | null
     */
    public static ?string $relatedResourceForTests = null;

    #[Unexposed]
    public static function getRelatedResource(): ?string
    {
        return static::$relatedResourceForTests ?? parent::getRelatedResource();
    }

    /**
     * Switched by the tests of the menu badges.
     */
    public static bool $menuBadge = true;

    public static function hasRelationNavigatorBadge(): bool
    {
        return static::$menuBadge;
    }

    /**
     * Switched by the tests of the "Open as page" action.
     *
     * @var (\Closure(Action): Action) | null
     */
    public static ?\Closure $configureRelationPageActionUsing = null;

    public static function configureRelationPageAction(Action $action): Action
    {
        return static::$configureRelationPageActionUsing ? (static::$configureRelationPageActionUsing)($action) : $action;
    }

    public static function hasRelationPageGlobalSearchAction(): bool
    {
        return static::$globalSearchAction;
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return $ownerRecord instanceof Author ? (string) $ownerRecord->posts()->count() : null;
    }

    /**
     * Set through PostsRelationManager::make(['note' => ...]).
     */
    public ?string $note = null;

    public function table(Table $table): Table
    {
        return $table
            ->description($this->note)
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
            ]);
    }
}
