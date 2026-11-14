<?php

use Illuminate\Support\Facades\File;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\AuthorResource;

it('creates the relation page next to the resource', function (): void {
    $path = dirname((new ReflectionClass(AuthorResource::class))->getFileName()).'/Pages/AuthorRelationPage.php';
    $original = File::get($path);

    try {
        $this->artisan('make:filament-relation-navigator', ['resource' => 'AuthorResource', '--force' => true])
            ->expectsOutputToContain('    ...AuthorRelationPage::relationRoutes(),')
            ->assertSuccessful();

        expect(File::get($path))
            ->toContain('namespace Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages;')
            ->toContain('class AuthorRelationPage extends RelationPage')
            ->toContain('protected static string $resource = AuthorResource::class;');
    } finally {
        File::put($path, $original);
    }
});

it('refuses to overwrite without --force', function (): void {
    $this->artisan('make:filament-relation-navigator', ['resource' => AuthorResource::class])
        ->assertFailed();
});
