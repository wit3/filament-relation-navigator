<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\KeyedAuthors\Pages;

use Filament\Resources\Pages\ListRecords;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\KeyedAuthors\KeyedAuthorResource;

class ListKeyedAuthors extends ListRecords
{
    protected static string $resource = KeyedAuthorResource::class;
}
