<?php

namespace Wit3\FilamentRelationNavigator;

use Filament\Resources\Resource;
use Illuminate\Support\Str;
use RuntimeException;
use Wit3\FilamentRelationNavigator\Pages\RelationPage;

/**
 * Relation page classes written on demand, like Laravel's real-time facades:
 * `Wit3\FilamentRelationNavigator\Generated\App\...\UserResourceRelationPage`
 * is a three-line subclass of {@see RelationPage} for `App\...\UserResource`,
 * stored in `storage/framework/cache` and loaded by an autoloader.
 */
final class GeneratedRelationPages
{
    public const NAMESPACE_PREFIX = 'Wit3\\FilamentRelationNavigator\\Generated\\';

    public const CLASS_SUFFIX = 'RelationPage';

    /**
     * Bump it when the generated code changes, so old files are not reused.
     */
    private const VERSION = 1;

    private static ?string $cachePath = null;

    public static function useCachePath(?string $path): void
    {
        self::$cachePath = $path;
    }

    /**
     * @internal Package plumbing: not covered by semantic versioning.
     *
     * The name of the generated class; it is written when first autoloaded.
     *
     * @param  class-string<resource>  $resource
     */
    public static function classFor(string $resource): string
    {
        return self::NAMESPACE_PREFIX.ltrim($resource, '\\').self::CLASS_SUFFIX;
    }

    /**
     * @internal Package plumbing: not covered by semantic versioning.
     *
     * @return class-string<resource> | null
     */
    public static function resourceFor(string $class): ?string
    {
        if (! str_starts_with($class, self::NAMESPACE_PREFIX) || ! str_ends_with($class, self::CLASS_SUFFIX)) {
            return null;
        }

        $resource = Str::beforeLast(Str::after($class, self::NAMESPACE_PREFIX), self::CLASS_SUFFIX);

        return is_subclass_of($resource, Resource::class) ? $resource : null;
    }

    /**
     * @internal Package plumbing: not covered by semantic versioning.
     */
    public static function isGenerated(string $class): bool
    {
        return str_starts_with($class, self::NAMESPACE_PREFIX);
    }

    /**
     * @internal Package plumbing: not covered by semantic versioning.
     *
     * The autoloader, registered by the service provider.
     */
    public static function load(string $class): void
    {
        $resource = self::resourceFor($class);

        if ($resource === null) {
            return;
        }

        require_once self::ensureFile($class, $resource);
    }

    /**
     * @internal Package plumbing: not covered by semantic versioning.
     */
    public static function pathFor(string $class): string
    {
        return rtrim(self::directory(), '/').'/relation-page-'.sha1($class.'|'.self::VERSION).'.php';
    }

    /**
     * `storage/framework/cache`, or the system temporary directory when the
     * storage is read-only (read-only containers, serverless).
     */
    private static function directory(): string
    {
        if (self::$cachePath !== null) {
            return self::$cachePath;
        }

        $storage = app()->storagePath('framework/cache');

        if (is_dir($storage) && is_writable($storage)) {
            return $storage;
        }

        return self::temporaryDirectory();
    }

    /**
     * A directory of this user only, inside the shared temporary directory:
     * nobody else may create or change the files the autoloader includes.
     */
    private static function temporaryDirectory(): string
    {
        $user = self::currentUserId();
        $directory = rtrim(sys_get_temp_dir(), '/').'/filament-relation-navigator-'.$user;

        if (! is_dir($directory)) {
            @mkdir($directory, 0700, true);
        }

        $isSafe = is_dir($directory)
            && ! is_link($directory)
            && @fileowner($directory) === $user
            && ((@fileperms($directory) ?: 0) & 0022) === 0;

        if (! $isSafe) {
            throw new RuntimeException("The relation page classes need a directory that only this user can write, and [{$directory}] is not (another user may have created it). Make storage/framework/cache writable, remove [{$directory}], or choose a directory with GeneratedRelationPages::useCachePath() in a service provider.");
        }

        return $directory;
    }

    /**
     * The user PHP runs as. Without ext-posix, getmyuid() would give the owner
     * of the script file instead: ask the owner of a file just created.
     */
    private static function currentUserId(): int
    {
        if (function_exists('posix_geteuid')) {
            return posix_geteuid();
        }

        $probe = @tempnam(sys_get_temp_dir(), 'frn');

        if ($probe === false) {
            return (int) getmyuid();
        }

        $owner = @fileowner($probe);
        @unlink($probe);

        return $owner === false ? (int) getmyuid() : $owner;
    }

    /**
     * @internal Called by the autoloader with a validated class name.
     *
     * The file of a generated class, written when missing or different from
     * the code it must hold: a file changed by someone else is replaced, not
     * included.
     *
     * @param  class-string<resource>  $resource
     */
    public static function ensureFile(string $class, string $resource): string
    {
        $path = self::pathFor($class);
        $code = self::code($class, $resource);

        if (is_file($path) && ! is_link($path) && @file_get_contents($path) === $code) {
            return $path;
        }

        if (! is_dir($directory = dirname($path))) {
            @mkdir($directory, 0755, true);
        }

        $temporaryPath = $path.'.'.getmypid().'.'.bin2hex(random_bytes(4)).'.tmp';

        if (@file_put_contents($temporaryPath, $code) === false || ! @chmod($temporaryPath, 0644) || ! @rename($temporaryPath, $path)) {
            @unlink($temporaryPath);

            throw new RuntimeException("Cannot write the relation page class to [{$path}]. Make the directory writable, or choose another one with GeneratedRelationPages::useCachePath() in a service provider.");
        }

        return $path;
    }

    /**
     * @param  class-string<resource>  $resource
     */
    private static function code(string $class, string $resource): string
    {
        $namespace = Str::beforeLast($class, '\\');
        $basename = class_basename($class);
        $relationPage = RelationPage::class;

        return <<<PHP
            <?php

            namespace {$namespace};

            /**
             * Generated by wit3/filament-relation-navigator. Do not edit.
             */
            class {$basename} extends \\{$relationPage}
            {
                protected static string \$resource = \\{$resource}::class;
            }

            PHP;
    }
}
