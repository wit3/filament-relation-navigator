<?php

use Livewire\Livewire;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\User;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\PostsRelationManager;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\PlainAuthors\Pages\PlainAuthorRelationPage;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\PlainAuthors\PlainAuthorResource;

beforeEach(function (): void {
    $this->actingAs(User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret']));

    $this->author = Author::create(['name' => 'Ada Lovelace']);
    $this->author->posts()->create(['title' => 'Notes on the Analytical Engine']);
});

it('lets the resource choose the relations', function (): void {
    expect(array_keys(PlainAuthorRelationPage::getRelationPageDefinitions()))->toBe(['posts']);
});

it('keeps a crumb back to the record before the menu', function (): void {
    $page = Livewire::test(PlainAuthorRelationPage::class, ['record' => $this->author->getKey(), 'relationPage' => 'posts'])
        ->instance();

    $breadcrumbs = $page->getBreadcrumbs();
    $viewUrl = PlainAuthorResource::getUrl('view', ['record' => $this->author]);

    expect($breadcrumbs)->toHaveKey($viewUrl)
        ->and($breadcrumbs[$viewUrl])->toBe('Author')
        ->and(array_search($viewUrl, array_keys($breadcrumbs), true))->toBe(count($breadcrumbs) - 2)
        ->and((string) end($breadcrumbs))->toContain('fi-relation-navigator')
        ->and($page->getSubheading())->toBeNull()
        ->and($page->getTitle())->toBe('Posts');
});

it('serves the relation page', function (): void {
    $this->get("/admin/plain-authors/{$this->author->getKey()}/posts")
        ->assertOk()
        ->assertSeeLivewire(PostsRelationManager::class);

    $this->get("/admin/plain-authors/{$this->author->getKey()}/published-comments")
        ->assertNotFound();
});
