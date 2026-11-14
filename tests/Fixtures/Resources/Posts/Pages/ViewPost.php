<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Posts\Pages;

use Filament\Resources\Pages\ViewRecord;
use Wit3\FilamentRelationNavigator\Concerns\HasRelationNavigator;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Posts\PostResource;

class ViewPost extends ViewRecord
{
    use HasRelationNavigator;

    protected static string $resource = PostResource::class;
}
