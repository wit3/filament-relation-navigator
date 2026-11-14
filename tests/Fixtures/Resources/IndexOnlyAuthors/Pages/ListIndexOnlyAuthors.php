<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\IndexOnlyAuthors\Pages;

use Filament\Resources\Pages\ListRecords;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\IndexOnlyAuthors\IndexOnlyAuthorResource;

class ListIndexOnlyAuthors extends ListRecords
{
    protected static string $resource = IndexOnlyAuthorResource::class;
}
