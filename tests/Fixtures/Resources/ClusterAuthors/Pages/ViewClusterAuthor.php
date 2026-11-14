<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\ClusterAuthors\Pages;

use Filament\Resources\Pages\ViewRecord;
use Wit3\FilamentRelationNavigator\Concerns\HasRelationNavigator;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\ClusterAuthors\ClusterAuthorResource;

class ViewClusterAuthor extends ViewRecord
{
    use HasRelationNavigator;

    protected static string $resource = ClusterAuthorResource::class;
}
