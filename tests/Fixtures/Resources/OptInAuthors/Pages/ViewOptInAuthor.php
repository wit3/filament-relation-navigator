<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\OptInAuthors\Pages;

use Filament\Resources\Pages\ViewRecord;
use Wit3\FilamentRelationNavigator\Concerns\HasRelationNavigator;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\OptInAuthors\OptInAuthorResource;

class ViewOptInAuthor extends ViewRecord
{
    use HasRelationNavigator;

    protected static string $resource = OptInAuthorResource::class;
}
