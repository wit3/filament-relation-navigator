<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\PlainAuthors\Pages;

use Filament\Resources\Pages\ViewRecord;
use Wit3\FilamentRelationNavigator\Concerns\HasRelationNavigator;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\PlainAuthors\PlainAuthorResource;

class ViewPlainAuthor extends ViewRecord
{
    use HasRelationNavigator;

    protected static string $resource = PlainAuthorResource::class;
}
