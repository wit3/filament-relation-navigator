<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\ConfiguredAuthors\Pages;

use Filament\Resources\Pages\ViewRecord;
use Wit3\FilamentRelationNavigator\Concerns\HasRelationNavigator;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\ConfiguredAuthors\ConfiguredAuthorResource;

class ViewConfiguredAuthor extends ViewRecord
{
    use HasRelationNavigator;

    protected static string $resource = ConfiguredAuthorResource::class;
}
