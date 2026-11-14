<?php

namespace Wit3\FilamentRelationNavigator;

use Filament\Panel;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
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
        FilamentAsset::register([
            Css::make('filament-relation-navigator', __DIR__.'/../resources/css/relation-navigator.css'),
        ], package: 'wit3/filament-relation-navigator');
    }
}
