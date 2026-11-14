<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\AuthorResource;

/**
 * A custom record page without the trait: not in the menu.
 */
class AuthorHistory extends Page
{
    use InteractsWithRecord;

    protected static string $resource = AuthorResource::class;

    protected static ?string $navigationLabel = 'Author history';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
}
