<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\UnrelatedAuthors\Pages;

use Filament\Resources\Pages\ListRecords;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\UnrelatedAuthors\UnrelatedAuthorResource;

class ListUnrelatedAuthors extends ListRecords
{
    protected static string $resource = UnrelatedAuthorResource::class;
}
