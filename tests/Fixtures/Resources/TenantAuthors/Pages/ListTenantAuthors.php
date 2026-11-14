<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\TenantAuthors\Pages;

use Filament\Resources\Pages\ListRecords;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\TenantAuthors\TenantAuthorResource;

class ListTenantAuthors extends ListRecords
{
    protected static string $resource = TenantAuthorResource::class;
}
