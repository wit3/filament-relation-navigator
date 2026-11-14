<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Wit3\FilamentRelationNavigator\Concerns\InteractsWithRelationPage;

class OptedInCommentsRelationManager extends PublishedCommentsRelationManager
{
    use InteractsWithRelationPage;

    public static function relationPageHeader(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('name')->label('Comments by'),
        ]);
    }
}
