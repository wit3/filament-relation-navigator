<?php

namespace Wit3\FilamentRelationNavigator;

use Filament\Panel;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Wit3\FilamentRelationNavigator\Commands\MakeMenuPresetCommand;
use Wit3\FilamentRelationNavigator\Commands\MakeRelationPageCommand;

class FilamentRelationNavigatorServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-relation-navigator';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasCommands([
                MakeRelationPageCommand::class,
                MakeMenuPresetCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        spl_autoload_register(GeneratedRelationPages::load(...));

        // Every panel registers the generated relation pages next to its
        // resources' own routes: nothing to add to the panel provider.
        $this->app->afterResolving(Panel::class, function (Panel $panel): void {
            $panel->authenticatedTenantRoutes(fn (Panel $panel) => RelationNavigator::registerRoutes($panel));
        });

        $this->app->booted(fn () => RelationNavigator::registerLivewireComponents());
    }

    public function packageBooted(): void
    {
        // The menu on small screens, above the heading of the pages that
        // use HasRelationNavigator.
        FilamentView::registerRenderHook(
            PanelsRenderHook::PAGE_HEADER_HEADING_BEFORE,
            fn (): ?Htmlable => RelationNavigator::renderMobileMenu(),
        );

        // Previous and next record, before the header actions.
        FilamentView::registerRenderHook(
            PanelsRenderHook::PAGE_HEADER_ACTIONS_BEFORE,
            fn (): ?Htmlable => RelationNavigator::renderRecordNavigation(),
        );

        // "Open as page" in the header of the relation managers' tables. A
        // link, never mounted: it only has to be there when the table renders,
        // lazy loading included (which builds the table again).
        Livewire::listen('render', function (mixed $component): void {
            if ($component instanceof RelationManager) {
                RelationNavigator::addRelationPageAction($component);
            }
        });

        FilamentAsset::register([
            Css::make('filament-relation-navigator', __DIR__.'/../resources/css/relation-navigator.css'),
        ], package: 'wit3/filament-relation-navigator');
    }
}
