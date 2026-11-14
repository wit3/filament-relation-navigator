<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages;

use Filament\Resources\Pages\ListRecords;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\AuthorResource;

class ListAuthors extends ListRecords
{
    protected static string $resource = AuthorResource::class;
}
