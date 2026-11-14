<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\AutoAuthors\Pages;

use Filament\Resources\Pages\ViewRecord;
use Wit3\FilamentRelationNavigator\Concerns\HasRelationNavigator;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\AutoAuthors\AutoAuthorResource;

class ViewAutoAuthor extends ViewRecord
{
    use HasRelationNavigator;

    protected static string $resource = AutoAuthorResource::class;
}
