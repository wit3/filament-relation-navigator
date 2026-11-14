<?php

use Livewire\Livewire;
use Wit3\FilamentRelationNavigator\RelationNavigator;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\User;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\AuthorResource;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\EditAuthor;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\ViewAuthor;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\HiddenPageRelationManager;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\PostsRelationManager;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\PublishedCommentsRelationManager;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\AutoAuthors\AutoAuthorResource;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\AutoAuthors\Pages\ViewAutoAuthor;

beforeEach(function (): void {
    $this->actingAs(User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret']));

    $this->author = Author::create(['name' => 'Ada Lovelace']);
    $this->author->posts()->create(['title' => 'Notes on the Analytical Engine']);
});

it('links a relation manager on the view and edit pages to its page', function (string $pageClass): void {
    $url = AuthorResource::getUrl('relations.posts', ['record' => $this->author]);

    Livewire::test(PostsRelationManager::class, ['ownerRecord' => $this->author, 'pageClass' => $pageClass])
        ->assertSee('fi-relation-navigator-rm-link', escape: false)
        ->assertSee($url, escape: false)
        ->assertSee('Open as page');
})->with([
    'view' => ViewAuthor::class,
    'edit' => EditAuthor::class,
]);

it('links a relation manager without a key to its slug', function (): void {
    Livewire::test(PublishedCommentsRelationManager::class, ['ownerRecord' => $this->author, 'pageClass' => ViewAuthor::class])
        ->assertSee(AuthorResource::getUrl('relations.published-comments', ['record' => $this->author]), escape: false);
});

it('does not link a relation manager to the relation page it is on', function (): void {
    $relationPage = RelationNavigator::getRelationPageClass(AuthorResource::class);

    Livewire::test(PostsRelationManager::class, ['ownerRecord' => $this->author, 'pageClass' => $relationPage])
        ->assertDontSee('fi-relation-navigator-rm-link', escape: false);

    $this->get("/admin/authors/{$this->author->getKey()}/posts")
        ->assertOk()
        ->assertDontSee('fi-relation-navigator-rm-link', escape: false);
});

it('does not link a relation manager without a page', function (): void {
    Livewire::test(HiddenPageRelationManager::class, ['ownerRecord' => $this->author, 'pageClass' => ViewAuthor::class])
        ->assertDontSee('fi-relation-navigator-rm-link', escape: false);
});

it('tells apart two entries of the same relation manager by their properties', function (): void {
    $test = fn (array $properties) => Livewire::test(
        PostsRelationManager::class,
        ['ownerRecord' => $this->author, 'pageClass' => ViewAutoAuthor::class, ...$properties],
    );

    $plain = AutoAuthorResource::getUrl('relations.posts', ['record' => $this->author]);
    $featured = AutoAuthorResource::getUrl('relations.featured-posts', ['record' => $this->author]);

    $test([])->assertSee($plain, escape: false)->assertDontSee($featured, escape: false);
    $test(['note' => 'Only the featured ones'])->assertSee($featured, escape: false);
});

it('turns the link off', function (): void {
    config()->set('filament-relation-navigator.relation_manager_link', false);

    Livewire::test(PostsRelationManager::class, ['ownerRecord' => $this->author, 'pageClass' => ViewAuthor::class])
        ->assertDontSee('fi-relation-navigator-rm-link', escape: false);
});
