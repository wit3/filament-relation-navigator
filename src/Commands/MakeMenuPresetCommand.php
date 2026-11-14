<?php

namespace Wit3\FilamentRelationNavigator\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeMenuPresetCommand extends Command
{
    protected $signature = 'make:filament-relation-navigator-preset
        {name : The preset name, e.g. compact}
        {--from=simple : The preset to start from (simple or github)}
        {--F|force : Overwrite the view if it exists}';

    protected $description = 'Create a breadcrumb menu preset of your own, starting from one of the package presets';

    public function handle(Filesystem $files): int
    {
        $name = Str::kebab((string) $this->argument('name'));
        $from = (string) $this->option('from');
        $source = __DIR__."/../../resources/views/menu/{$from}.blade.php";

        if (! $files->exists($source)) {
            $this->components->error("There is no [{$from}] preset to start from. Use simple or github.");

            return self::FAILURE;
        }

        $view = "filament.relation-navigator.{$name}";
        $path = resource_path("views/filament/relation-navigator/{$name}.blade.php");

        if ($files->exists($path) && (! $this->option('force'))) {
            $this->components->error("[{$path}] already exists. Use --force to overwrite it.");

            return self::FAILURE;
        }

        $files->ensureDirectoryExists(dirname($path));
        $files->copy($source, $path);

        $this->components->info("Preset view [{$path}] created from [{$from}].");

        $this->line('  Register it in <comment>config/filament-relation-navigator.php</comment>:');
        $this->newLine();
        $this->line("    'preset' => '{$name}',");
        $this->line("    'presets' => [..., '{$name}' => '{$view}'],");

        return self::SUCCESS;
    }
}
