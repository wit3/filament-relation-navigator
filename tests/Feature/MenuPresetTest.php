<?php

use Illuminate\Support\Facades\View;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\User;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\ViewAuthor;

beforeEach(function (): void {
    $this->actingAs(User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret']));

    $this->author = Author::create(['name' => 'Ada Lovelace']);
    $this->author->posts()->create(['title' => 'Notes on the Analytical Engine']);
});

it('uses the dropdown preset by default', function (): void {
    $this->get("/admin/authors/{$this->author->getKey()}")
        ->assertOk()
        ->assertSee('fi-relation-navigator-preset-dropdown', escape: false)
        ->assertDontSee('data-relation-navigator-search', escape: false);
});

it('renders the searchable preset with a heading, a search field and the current page checked', function (): void {
    config()->set('filament-relation-navigator.menu.preset', 'searchable');

    $this->get("/admin/authors/{$this->author->getKey()}/posts")
        ->assertOk()
        ->assertSee('fi-relation-navigator-preset-searchable', escape: false)
        ->assertSee('fi-relation-navigator-caret', escape: false)
        ->assertSee('Switch page')
        ->assertSee('x-model="search"', escape: false)
        ->assertSee('Find a page…')
        ->assertSee('fi-relation-navigator-check', escape: false);
});

it('hides the search field below the configured number of items', function (): void {
    config()->set('filament-relation-navigator.menu.preset', 'searchable');
    config()->set('filament-relation-navigator.menu.search_from', 50);

    $this->get("/admin/authors/{$this->author->getKey()}/posts")
        ->assertOk()
        ->assertSee('Switch page')
        ->assertDontSee('x-model="search"', escape: false);
});

it('renders a preset of your own', function (): void {
    View::addNamespace('frn-tests', __DIR__.'/../Fixtures/views');

    config()->set('filament-relation-navigator.menu.presets.mine', 'frn-tests::custom-menu');
    config()->set('filament-relation-navigator.menu.preset', 'mine');

    $this->get("/admin/authors/{$this->author->getKey()}/posts")
        ->assertOk()
        ->assertSee('my-custom-menu', escape: false)
        ->assertSee('Posts (6)');
});

it('turns the badges off from the config', function (): void {
    config()->set('filament-relation-navigator.menu.badges', false);

    $page = Livewire\Livewire::test(
        ViewAuthor::class,
        ['record' => $this->author->getKey()],
    )->instance();

    expect(collect($page->getRelationNavigatorItems())->firstWhere('key', 'relations.posts')->badge)->toBeNull();
});
