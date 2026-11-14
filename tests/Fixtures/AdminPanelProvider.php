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
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\AuthorResource;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\PlainAuthors\PlainAuthorResource;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->resources([
                AuthorResource::class,
                PlainAuthorResource::class,
                Resources\AutoAuthors\AutoAuthorResource::class,
                Resources\OptInAuthors\OptInAuthorResource::class,
                Resources\NoPagesAuthors\NoPagesAuthorResource::class,
                Resources\Posts\PostResource::class,
                Resources\ClusterAuthors\ClusterAuthorResource::class,
            ])
            ->pages([Clusters\Settings::class])
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
