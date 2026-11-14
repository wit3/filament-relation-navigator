<?php

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Gate;
use Wit3\FilamentRelationNavigator\RelationNavigator;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\User;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Policies\AuthorPolicy;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\AuthorResource;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\PostsRelationManager;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\AutoAuthors\AutoAuthorResource;

beforeEach(function (): void {
    $this->actingAs(User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret']));

    Filament::setCurrentPanel(Filament::getPanel('admin'));

    $this->author = Author::create(['name' => 'Ada Lovelace']);
});

afterEach(function (): void {
    PostsRelationManager::$globalSearchAction = true;
});

/**
 * @param  array<Action>  $actions
 * @return array<string, string>
 */
function actionLinks(array $actions): array
{
    return collect($actions)->mapWithKeys(fn (Action $action): array => [(string) $action->getLabel() => (string) $action->getUrl()])->all();
}

it('adds no links by default', function (): void {
    expect(AutoAuthorResource::getGlobalSearchResultActions($this->author))->toBe([]);
});

it('links a global search result to the relation pages the user can see', function (): void {
    config()->set('filament-relation-navigator.global_search_actions', true);

    $urls = collect(AutoAuthorResource::getGlobalSearchResultActions($this->author))->map->getUrl()->all();

    expect($urls)->toBe([
        AutoAuthorResource::getUrl('relations.posts', ['record' => $this->author]),
        AutoAuthorResource::getUrl('relations.featured-posts', ['record' => $this->author]),
    ]);
});

it('shows at most the configured number of links', function (): void {
    config()->set('filament-relation-navigator.global_search_actions', 1);

    expect(AutoAuthorResource::getGlobalSearchResultActions($this->author))->toHaveCount(1);
});

it('leaves out a relation manager that opts out', function (): void {
    config()->set('filament-relation-navigator.global_search_actions', true);

    $before = collect(RelationNavigator::getGlobalSearchResultActions(AuthorResource::class, $this->author))->map->getUrl();

    PostsRelationManager::$globalSearchAction = false;

    $after = collect(RelationNavigator::getGlobalSearchResultActions(AuthorResource::class, $this->author))->map->getUrl();

    expect($before)->toContain(AuthorResource::getUrl('relations.posts', ['record' => $this->author]))
        ->and($after)->not->toContain(AuthorResource::getUrl('relations.posts', ['record' => $this->author]))
        ->and($after->count())->toBe($before->count() - 1);
});

it('turns the links on or off per resource', function (): void {
    AuthorResource::$options = ['hasRelationPageGlobalSearchActions' => true];

    try {
        expect(RelationNavigator::getGlobalSearchResultActions(AuthorResource::class, $this->author))->not->toBeEmpty()
            // Another resource keeps the config (off).
            ->and(AutoAuthorResource::getGlobalSearchResultActions($this->author))->toBe([]);
    } finally {
        AuthorResource::$options = [];
    }
});

it('adds no links to a record the user cannot view', function (): void {
    config()->set('filament-relation-navigator.global_search_actions', true);

    Gate::policy(Author::class, AuthorPolicy::class);

    $secret = Author::create(['name' => 'Secret']);

    expect(AutoAuthorResource::getGlobalSearchResultActions($secret))->toBe([]);
});

it('shows the links in Filament global search results', function (): void {
    config()->set('filament-relation-navigator.global_search_actions', true);

    // What Filament's global search shows for this resource.
    $result = AutoAuthorResource::getGlobalSearchResults('Lovelace')->first();

    expect($result)->not->toBeNull()
        ->and(collect($result->actions)->map->getUrl()->all())
        ->toContain(AutoAuthorResource::getUrl('relations.posts', ['record' => $this->author]));
});
