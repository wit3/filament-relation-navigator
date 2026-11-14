<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
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
                TextColumn::make('title')->searchable(),
            ]);
    }
}
