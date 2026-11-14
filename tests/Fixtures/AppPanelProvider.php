<?php

namespace Wit3\FilamentRelationNavigator\Tests\Fixtures;

use Filament\Http\Middleware\Authenticate;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Team;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\TenantAuthors\TenantAuthorResource;

/**
 * A second panel with tenancy and SPA mode.
 */
class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('app')
            ->path('app')
            ->tenant(Team::class)
            ->spa()
            ->resources([TenantAuthorResource::class])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                SubstituteBindings::class,
            ])
            ->authMiddleware([Authenticate::class]);
    }
}
