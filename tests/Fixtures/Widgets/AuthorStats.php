<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;

class AuthorStats extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    public ?Model $record = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Posts written', $this->record instanceof Author ? (string) $this->record->posts()->count() : '0'),
        ];
    }
}
