<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\PlainAuthors\Pages;

use Filament\Resources\Pages\ListRecords;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\PlainAuthors\PlainAuthorResource;

class ListPlainAuthors extends ListRecords
{
    protected static string $resource = PlainAuthorResource::class;
}
