<?php

namespace Wit3\FilamentRelationNavigator\Commands;

use Filament\Facades\Filament;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use ReflectionClass;

class MakeRelationPageCommand extends Command
{
    protected $signature = 'make:filament-relation-navigator
        {resource : The resource class (FQCN) or its class name, e.g. UserResource}
        {--F|force : Overwrite the page if it exists}';

    protected $description = 'Create the relation page of a Filament resource, one page per relation manager';

    public function handle(Filesystem $files): int
    {
        $resource = $this->resolveResource((string) $this->argument('resource'));

        if (! $resource) {
            $this->components->error('Resource not found. Pass its full class name, e.g. App\\Filament\\Resources\\Users\\UserResource.');

            return self::FAILURE;
        }

        $resourceBasename = class_basename($resource);
        $class = Str::beforeLast($resourceBasename, 'Resource').'RelationPage';
        $namespace = Str::beforeLast($resource, '\\').'\\Pages';
        $path = dirname((new ReflectionClass($resource))->getFileName())."/Pages/{$class}.php";

        if ($files->exists($path) && (! $this->option('force'))) {
            $this->components->error("[{$path}] already exists. Use --force to overwrite it.");

            return self::FAILURE;
        }

        $stub = $files->get(__DIR__.'/../../stubs/relation-page.stub');

        $files->ensureDirectoryExists(dirname($path));
        $files->put($path, str_replace(
            ['{{ namespace }}', '{{ resource }}', '{{ class }}', '{{ resourceClass }}'],
            [$namespace, $resource, $class, $resourceBasename],
            $stub,
        ));

        $this->components->info("Relation page [{$path}] created.");

        $this->line('  Add its routes to <comment>'.$resourceBasename.'::getPages()</comment>:');
        $this->newLine();
        $this->line("    ...{$class}::relationRoutes(),");
        $this->newLine();
        $this->line('  For the breadcrumb menu, add <comment>use \\Wit3\\FilamentRelationNavigator\\Concerns\\HasRelationNavigator;</comment>');
        $this->line('  to the resource\'s view and edit pages.');

        return self::SUCCESS;
    }

    protected function resolveResource(string $resource): ?string
    {
        $resource = ltrim($resource, '\\');

        if (class_exists($resource)) {
            return $resource;
        }

        $name = Str::finish(class_basename($resource), 'Resource');

        foreach (Filament::getPanels() as $panel) {
            foreach ($panel->getResources() as $panelResource) {
                if (class_basename($panelResource) === $name) {
                    return $panelResource;
                }
            }
        }

        return null;
    }
}
