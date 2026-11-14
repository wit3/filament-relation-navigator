<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages;

use Filament\Resources\Pages\EditRecord;
use Wit3\FilamentRelationNavigator\Concerns\HasRelationNavigator;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\AuthorResource;

class EditAuthor extends EditRecord
{
    use HasRelationNavigator;

    protected static string $resource = AuthorResource::class;

    /**
     * A page that already builds its own breadcrumbs.
     *
     * @return array<string | int, mixed>
     */
    public function getBreadcrumbs(): array
    {
        return $this->withRelationNavigator([
            AuthorResource::getUrl('index') => 'Custom authors',
            'Custom edit',
        ]);
    }
}
