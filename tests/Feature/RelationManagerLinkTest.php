<?php

use Livewire\Livewire;
use Wit3\FilamentRelationNavigator\Concerns\InteractsWithRelationPage;
use Wit3\FilamentRelationNavigator\RelationNavigator;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\User;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\AuthorResource;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\EditAuthor;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\ViewAuthor;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\GroupOnlyCommentsRelationManager;
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

it('does not link a relation manager to the relation page it is on', function (): void {
    // There the relation manager gets the view page as its page class, as on
    // the view page: only the URL tells them apart.
    $this->get("/admin/authors/{$this->author->getKey()}/published-comments")
        ->assertOk()
        ->assertSee('Published comments')
        ->assertDontSee('Open as page');
});

it('links a relation manager on a group page to its own page', function (): void {
    $id = $this->author->getKey();

    // The group page shows its relation managers one under the other.
    // Published comments has its own page: linked. Group only comments has
    // only the group's, the page it is on: not linked. (Posts is lazy.)
    $html = $this->get("/admin/authors/{$id}/activity")->assertOk()->getContent();

    expect($html)->toContain('Group only comments')
        ->toContain("/admin/authors/{$id}/published-comments")
        ->and(substr_count($html, 'Open as page'))->toBe(1);
});

it('prefers the own page of a relation manager to the page of its group', function (): void {
    Livewire::test(PublishedCommentsRelationManager::class, ['ownerRecord' => $this->author, 'pageClass' => ViewAuthor::class])
        ->assertSee(AuthorResource::getUrl('relations.published-comments', ['record' => $this->author]), escape: false);

    Livewire::test(GroupOnlyCommentsRelationManager::class, ['ownerRecord' => $this->author, 'pageClass' => ViewAuthor::class])
        ->assertSee(AuthorResource::getUrl('relations.activity', ['record' => $this->author]), escape: false);
});

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

describe('customising the action', function (): void {
    afterEach(function (): void {
        RelationNavigator::flushRelationPageActionConfigurations();
        AuthorResource::$options = [];
        PostsRelationManager::$configureRelationPageActionUsing = null;
    });

    $render = fn () => Livewire::test(PostsRelationManager::class, ['ownerRecord' => test()->author, 'pageClass' => ViewAuthor::class]);

    it('is a link by default', function () use ($render): void {
        $html = $render()->html();

        expect($html)->toMatch('#<a[^>]+class="[^"]*fi-link[^"]*"[^>]*>[\s\S]*?Open as page#');
    });

    it('becomes a button or an icon button from the config', function (string $style, string $class) use ($render): void {
        config()->set('filament-relation-navigator.relation_manager_action.style', $style);

        expect($render()->html())->toMatch('#<a[^>]+class="[^"]*'.$class.'[^"]*"#');
    })->with([
        'button' => ['button', 'fi-btn'],
        'icon button' => ['icon_button', 'fi-icon-btn'],
    ]);

    it('takes its label, icon and target from the config', function () use ($render): void {
        config()->set('filament-relation-navigator.relation_manager_action', [
            'label' => 'Full page',
            'icon' => null,
            'new_tab' => true,
        ]);

        $action = RelationNavigator::makeRelationPageAction($render()->instance());

        expect($action->getLabel())->toBe('Full page')
            ->and($action->getIcon())->toBeNull()
            ->and($action->shouldOpenUrlInNewTab())->toBeTrue();
    });

    it('can be changed from a service provider, a resource and a relation manager, in this order', function () use ($render): void {
        RelationNavigator::configureRelationPageActionUsing(fn ($action) => $action->label('global'));
        AuthorResource::$options['relationPageAction'] = fn ($action) => $action->label($action->getLabel().' > resource');
        PostsRelationManager::$configureRelationPageActionUsing = fn ($action) => $action->label($action->getLabel().' > manager');

        expect(RelationNavigator::makeRelationPageAction($render()->instance())->getLabel())->toBe('global > resource > manager')
            ->and($render()->html())->toContain('global &gt; resource &gt; manager');
    });
});
