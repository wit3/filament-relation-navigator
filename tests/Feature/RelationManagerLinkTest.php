<?php

use Livewire\Livewire;
use Wit3\FilamentRelationNavigator\Concerns\InteractsWithRelationPage;
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
        ->assertSee('Open as page')
        ->assertSee($url, escape: false);
})->with([
    'view' => ViewAuthor::class,
    'edit' => EditAuthor::class,
]);

it('links a relation manager without a key to its slug', function (): void {
    Livewire::test(PublishedCommentsRelationManager::class, ['ownerRecord' => $this->author, 'pageClass' => ViewAuthor::class])
        ->assertSee(AuthorResource::getUrl('relations.published-comments', ['record' => $this->author]), escape: false);
});

it('shows the link on the view page', function (): void {
    $id = $this->author->getKey();

    $this->get("/admin/authors/{$id}?relation=0")
        ->assertOk()
        ->assertSee('Open as page')
        ->assertSee("/admin/authors/{$id}/published-comments", escape: false);
});

it('does not link a relation manager to the relation page it is on', function (string $slug): void {
    // There the relation manager gets the view page as its page class, as on
    // the view page: only the URL tells them apart.
    $this->get("/admin/authors/{$this->author->getKey()}/{$slug}")
        ->assertOk()
        ->assertSee('Published comments')
        ->assertDontSee('Open as page');
})->with([
    'its own page' => 'published-comments',
    'the page of its group' => 'activity',
]);

it('does not link a relation manager on a relation page class', function (): void {
    Livewire::test(PostsRelationManager::class, [
        'ownerRecord' => $this->author,
        'pageClass' => RelationNavigator::getRelationPageClass(AuthorResource::class),
    ])->assertDontSee('Open as page');
});

it('does not link a relation manager without a page', function (): void {
    Livewire::test(HiddenPageRelationManager::class, ['ownerRecord' => $this->author, 'pageClass' => ViewAuthor::class])
        ->assertDontSee('Open as page');
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
        ->assertDontSee('Open as page');
});

it('puts the action first in the header of the table', function (): void {
    $component = Livewire::test(PostsRelationManager::class, ['ownerRecord' => $this->author, 'pageClass' => ViewAuthor::class]);

    $names = collect($component->instance()->getTable()->getHeaderActions())
        ->map(fn ($action) => $action->getName())
        ->all();

    expect($names[0])->toBe(RelationNavigator::RELATION_PAGE_ACTION)
        ->and(array_count_values($names)[RelationNavigator::RELATION_PAGE_ACTION])->toBe(1);
});

it('turns the action off on a relation manager', function (string $how): void {
    $manager = match ($how) {
        'property' => new class extends PostsRelationManager
        {
            use InteractsWithRelationPage;

            protected static bool $hasRelationPageLink = false;
        },
        'method' => new class extends PostsRelationManager
        {
            public static function hasRelationPageLink(): bool
            {
                return false;
            }
        },
    };

    $manager->ownerRecord = $this->author;
    $manager->pageClass = ViewAuthor::class;

    expect(RelationNavigator::hasRelationPageAction($manager))->toBeFalse();

    $plain = new PostsRelationManager;
    $plain->ownerRecord = $this->author;
    $plain->pageClass = ViewAuthor::class;

    expect(RelationNavigator::hasRelationPageAction($plain))->toBeTrue();
})->with(['property', 'method']);

it('turns the action off on a resource', function (): void {
    AuthorResource::$options = ['hasRelationPageLinks' => false];

    try {
        Livewire::test(PostsRelationManager::class, ['ownerRecord' => $this->author, 'pageClass' => ViewAuthor::class])
            ->assertDontSee('Open as page');
    } finally {
        AuthorResource::$options = [];
    }
});
