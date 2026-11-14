<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\NoPagesAuthors\Pages;

use Filament\Resources\Pages\ViewRecord;
use Wit3\FilamentRelationNavigator\Concerns\HasRelationNavigator;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\NoPagesAuthors\NoPagesAuthorResource;

class ViewNoPagesAuthor extends ViewRecord
{
    use HasRelationNavigator;

    protected static string $resource = NoPagesAuthorResource::class;
}
