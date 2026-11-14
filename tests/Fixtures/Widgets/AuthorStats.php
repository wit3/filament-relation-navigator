<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class AuthorStats extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    public ?Model $record = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Posts written', (string) $this->record?->posts()->count()),
        ];
    }
}
