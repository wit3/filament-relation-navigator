<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\ConfiguredAuthors\Pages;

use Filament\Resources\Pages\ListRecords;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\ConfiguredAuthors\ConfiguredAuthorResource;

class ListConfiguredAuthors extends ListRecords
{
    protected static string $resource = ConfiguredAuthorResource::class;
}
