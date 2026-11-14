<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\AuthorResource;

/**
 * A custom page whose path (/{record}/timeline) differs from its key.
 */
class AuthorTimeline extends Page
{
    use InteractsWithRecord;

    protected static string $resource = AuthorResource::class;

    protected static ?string $title = 'Author timeline page';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
}
