<?php

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Wit3\FilamentRelationNavigator\RelationNavigator;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\User;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\AuthorResource;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\AuthorRelationPage;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\ViewAuthor;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\PostsRelationManager;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\PublishedCommentsRelationManager;

beforeEach(function (): void {
    $this->actingAs(User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret']));

    $this->author = Author::create(['name' => 'Ada Lovelace']);
    $this->author->posts()->create(['title' => 'Notes on the Analytical Engine']);
    $this->author->posts()->create(['title' => 'Sketch of the engine']);
    $this->author->comments()->create(['body' => 'A published comment', 'is_published' => true]);
    $this->author->comments()->create(['body' => 'A draft comment', 'is_published' => false]);

    $this->other = Author::create(['name' => 'Charles Babbage']);
    $this->other->posts()->create(['title' => 'Passages from the life of a philosopher']);
});

it('derives one slug per relation manager', function (): void {
    expect(array_keys(AuthorRelationPage::getRelationPageDefinitions()))
        ->toBe(['posts', 'published-comments', 'notes', 'activity']);
});

it('registers one named route per relation page, after the record pages', function (): void {
    foreach (['posts', 'published-comments', 'notes', 'activity'] as $slug) {
        expect(Route::has("filament.admin.resources.authors.relations.{$slug}"))->toBeTrue();
    }

    expect(Route::has('filament.admin.resources.authors.relations.hidden-page'))->toBeFalse()
        ->and(AuthorResource::getUrl('relations.posts', ['record' => $this->author]))
        ->toEndWith("/admin/authors/{$this->author->getKey()}/posts");
});

it('renders the relation manager on its own page', function (): void {
    $this->get("/admin/authors/{$this->author->getKey()}/posts")
        ->assertOk()
        ->assertSee('fi-relation-navigator', escape: false)
        ->assertSeeLivewire(PostsRelationManager::class)
        ->assertDontSee('Passages from the life of a philosopher');
});

it('lists the related records in the relation manager', function (): void {
    Livewire::test(AuthorRelationPage::class, ['record' => $this->author->getKey(), 'relationPage' => 'posts'])
        ->assertOk()
        ->assertSet('relationPage', 'posts')
        ->call('$refresh')
        ->assertOk();

    Livewire::test(
        PostsRelationManager::class,
        ['ownerRecord' => $this->author, 'pageClass' => ViewAuthor::class],
    )
        ->assertSee('Notes on the Analytical Engine')
        ->assertDontSee('Passages from the life of a philosopher');
});

it('uses the derived slug of an unkeyed relation manager', function (): void {
    $this->get("/admin/authors/{$this->author->getKey()}/published-comments")
        ->assertOk()
        ->assertSee('Published comments');
});

it('renders every manager of a relation group', function (): void {
    $this->get("/admin/authors/{$this->author->getKey()}/activity")
        ->assertOk()
        ->assertSeeLivewire(PostsRelationManager::class)
        ->assertSeeLivewire(PublishedCommentsRelationManager::class);
});

it('forbids a relation the user cannot view', function (): void {
    $this->get("/admin/authors/{$this->author->getKey()}/notes")->assertForbidden();
});

it('returns 404 for an unknown relation', function (): void {
    Livewire::test(AuthorRelationPage::class, ['record' => $this->author->getKey(), 'relationPage' => 'nope'])
        ->assertNotFound();
});

it('does not swallow the other record pages', function (): void {
    $this->get("/admin/authors/{$this->author->getKey()}/edit")
        ->assertOk()
        ->assertSee('Custom edit');
});

it('builds the menu with the record pages and the visible relations', function (): void {
    $items = RelationNavigator::getItems(AuthorResource::class, $this->author, 'relations.posts');

    expect(collect($items)->pluck('key')->all())
        ->toBe(['view', 'edit', 'relations.posts', 'relations.published-comments', 'relations.activity'])
        ->and(collect($items)->firstWhere('key', 'relations.posts')->badge)->toBe('2')
        ->and(collect($items)->firstWhere('key', 'relations.posts')->isActive)->toBeTrue()
        ->and(collect($items)->firstWhere('key', 'view')->isActive)->toBeFalse();
});

it('adds the menu to the last breadcrumb of the view page', function (): void {
    $id = $this->author->getKey();

    $this->get("/admin/authors/{$id}")
        ->assertOk()
        ->assertSee('fi-relation-navigator', escape: false)
        ->assertSee("/admin/authors/{$id}/posts", escape: false)
        ->assertSee("/admin/authors/{$id}/published-comments", escape: false)
        ->assertDontSee("/admin/authors/{$id}/notes", escape: false);
});

it('wraps breadcrumbs that a page builds by itself', function (): void {
    $this->get("/admin/authors/{$this->author->getKey()}/edit")
        ->assertSee('Custom authors')
        ->assertSee('fi-relation-navigator', escape: false);
});

it('builds native record sub-navigation from the same items', function (): void {
    $page = Livewire::test(ViewAuthor::class, ['record' => $this->author->getKey()])->instance();

    $navigation = RelationNavigator::getRecordSubNavigation($page);

    expect($navigation)->toHaveCount(5)
        ->and($navigation[0]->isActive())->toBeTrue()
        ->and($navigation[2]->getUrl())->toEndWith("/authors/{$this->author->getKey()}/posts");
});
