<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\NoPagesAuthors\Pages;

use Filament\Resources\Pages\ListRecords;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\NoPagesAuthors\NoPagesAuthorResource;

class ListNoPagesAuthors extends ListRecords
{
    protected static string $resource = NoPagesAuthorResource::class;
}
