<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\AutoAuthors\Pages;

use Filament\Resources\Pages\ListRecords;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\AutoAuthors\AutoAuthorResource;

class ListAutoAuthors extends ListRecords
{
    protected static string $resource = AutoAuthorResource::class;
}
