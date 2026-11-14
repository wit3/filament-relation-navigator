<?php

namespace Wit3\FilamentRelationNavigator\Commands;

use Filament\Facades\Filament;
use Filament\Resources\Resource;
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
        $argument = $this->argument('resource');
        $resource = is_string($argument) ? $this->resolveResource($argument) : null;

        if (! $resource) {
            $this->components->error('Resource not found. Pass its full class name, e.g. App\\Filament\\Resources\\Users\\UserResource.');

            return self::FAILURE;
        }

        $resourceBasename = class_basename($resource);
        $class = Str::beforeLast($resourceBasename, 'Resource').'RelationPage';
        $namespace = Str::beforeLast($resource, '\\').'\\Pages';
        $resourcePath = (new ReflectionClass($resource))->getFileName();

        if ($resourcePath === false) {
            $this->components->error("Cannot find the file of [{$resource}].");

            return self::FAILURE;
        }

        $path = dirname($resourcePath)."/Pages/{$class}.php";

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

    /**
     * @return class-string<resource> | null
     */
    protected function resolveResource(string $resource): ?string
    {
        $resource = ltrim($resource, '\\');

        if (is_subclass_of($resource, Resource::class)) {
            return $resource;
        }

        $name = Str::finish(class_basename($resource), 'Resource');

        foreach (Filament::getPanels() as $panel) {
            foreach ($panel->getResources() as $panelResource) {
                if ((class_basename($panelResource) === $name) && is_subclass_of($panelResource, Resource::class)) {
                    return $panelResource;
                }
            }
        }

        return null;
    }
}
