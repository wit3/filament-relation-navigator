<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\KeyedAuthors\Pages;

use Filament\Resources\Pages\ViewRecord;
use Wit3\FilamentRelationNavigator\Concerns\HasRelationNavigator;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\KeyedAuthors\KeyedAuthorResource;

class ShowKeyedAuthor extends ViewRecord
{
    use HasRelationNavigator;

    protected static string $resource = KeyedAuthorResource::class;
}
