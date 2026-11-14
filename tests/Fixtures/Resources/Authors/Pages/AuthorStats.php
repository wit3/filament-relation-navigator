<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Wit3\FilamentRelationNavigator\Concerns\HasRelationNavigator;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\AuthorResource;

/**
 * A custom record page in the menu, using InteractsWithRecord itself.
 */
class AuthorStats extends Page
{
    use HasRelationNavigator, InteractsWithRecord {
        HasRelationNavigator::getBreadcrumbs insteadof InteractsWithRecord;
        InteractsWithRecord::getBreadcrumbs as getRecordBreadcrumbs;
    }

    protected static string $resource = AuthorResource::class;

    protected static ?string $navigationLabel = 'Author stats';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
}
