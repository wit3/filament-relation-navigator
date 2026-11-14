<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Posts;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Wit3\FilamentRelationNavigator\Concerns\HasRelationPages;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Post;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\AuthorResource;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Posts\Pages\ViewPost;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Posts\RelationManagers\RevisionsRelationManager;

/**
 * Nested under AuthorResource: /authors/{author}/posts/{record}.
 */
class PostResource extends Resource
{
    use HasRelationPages;

    protected static ?string $model = Post::class;

    protected static ?string $parentResource = AuthorResource::class;

    protected static ?string $recordTitleAttribute = 'title';

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function getRelations(): array
    {
        return [
            'revisions' => RevisionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'view' => ViewPost::route('/{record}'),
        ];
    }
}
