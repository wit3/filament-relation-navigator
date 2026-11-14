<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\TenantAuthors\Pages;

use Filament\Resources\Pages\ViewRecord;
use Wit3\FilamentRelationNavigator\Concerns\HasRelationNavigator;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\TenantAuthors\TenantAuthorResource;

class ViewTenantAuthor extends ViewRecord
{
    use HasRelationNavigator;

    protected static string $resource = TenantAuthorResource::class;
}
