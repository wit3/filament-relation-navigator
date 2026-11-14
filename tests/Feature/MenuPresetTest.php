<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Livewire\Livewire;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\User;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\ViewAuthor;

beforeEach(function (): void {
    $this->actingAs(User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret']));

    $this->author = Author::create(['name' => 'Ada Lovelace']);
    $this->author->posts()->create(['title' => 'Notes on the Analytical Engine']);
});

it('uses the simple preset without search by default', function (): void {
    $this->get("/admin/authors/{$this->author->getKey()}")
        ->assertOk()
        ->assertSee('fi-relation-navigator-preset-simple', escape: false)
        ->assertDontSee('x-model="search"', escape: false);
});

it('adds the search field to the simple preset', function (): void {
    config()->set('filament-relation-navigator.menu.searchable', true);

    $this->get("/admin/authors/{$this->author->getKey()}")
        ->assertOk()
        ->assertSee('fi-relation-navigator-preset-simple', escape: false)
        ->assertSee('x-model="search"', escape: false)
        ->assertSee('Find a page…')
        ->assertDontSee('Switch page');
});

it('renders the github preset with a caret, a heading and the current page checked', function (): void {
    config()->set('filament-relation-navigator.menu.preset', 'github');

    $this->get("/admin/authors/{$this->author->getKey()}/posts")
        ->assertOk()
        ->assertSee('fi-relation-navigator-preset-github', escape: false)
        ->assertSee('fi-relation-navigator-caret', escape: false)
        ->assertSee('Switch page')
        ->assertSee('fi-relation-navigator-check', escape: false)
        ->assertDontSee('x-model="search"', escape: false);
});

it('adds the search field to the github preset, from the configured number of items', function (): void {
    config()->set('filament-relation-navigator.menu.preset', 'github');
    config()->set('filament-relation-navigator.menu.searchable', true);

    $this->get("/admin/authors/{$this->author->getKey()}/posts")
        ->assertSee('x-model="search"', escape: false);

    config()->set('filament-relation-navigator.menu.search_from', 50);

    $this->get("/admin/authors/{$this->author->getKey()}/posts")
        ->assertDontSee('x-model="search"', escape: false);
});

it('renders a preset of your own, from the presets or by view name', function (): void {
    View::addNamespace('frn-tests', __DIR__.'/../Fixtures/views');

    config()->set('filament-relation-navigator.menu.presets.mine', 'frn-tests::custom-menu');
    config()->set('filament-relation-navigator.menu.preset', 'mine');

    $this->get("/admin/authors/{$this->author->getKey()}/posts")
        ->assertOk()
        ->assertSee('my-custom-menu', escape: false)
        ->assertSee('Posts (6)');

    config()->set('filament-relation-navigator.menu.preset', 'frn-tests::custom-menu');

    $this->get("/admin/authors/{$this->author->getKey()}/posts")
        ->assertSee('my-custom-menu', escape: false);
});

it('creates a preset of your own from a package preset', function (): void {
    $path = resource_path('views/filament/relation-navigator/compact.blade.php');
    File::delete($path);

    try {
        $this->artisan('make:filament-relation-navigator-preset', ['name' => 'compact', '--from' => 'github'])
            ->expectsOutputToContain("'compact' => 'filament.relation-navigator.compact'")
            ->assertSuccessful();

        expect(File::get($path))->toContain('fi-relation-navigator-preset-github');

        config()->set('filament-relation-navigator.menu.presets.compact', 'filament.relation-navigator.compact');
        config()->set('filament-relation-navigator.menu.preset', 'compact');

        $this->get("/admin/authors/{$this->author->getKey()}/posts")
            ->assertOk()
            ->assertSee('Switch page');

        $this->artisan('make:filament-relation-navigator-preset', ['name' => 'nope', '--from' => 'missing'])
            ->assertFailed();
    } finally {
        File::delete($path);
    }
});

it('turns the badges off from the config', function (): void {
    config()->set('filament-relation-navigator.menu.badges', false);

    $page = Livewire::test(ViewAuthor::class, ['record' => $this->author->getKey()])->instance();

    expect(collect($page->getRelationNavigatorItems())->firstWhere('key', 'relations.posts')->badge)->toBeNull();
});

it('explains a preset that points to a missing view', function (): void {
    config()->set('filament-relation-navigator.menu.preset', 'does-not-exist');

    $this->withoutExceptionHandling();

    expect(fn () => $this->get("/admin/authors/{$this->author->getKey()}"))
        ->toThrow('does not exist. Check `filament-relation-navigator.menu.preset`');
});
