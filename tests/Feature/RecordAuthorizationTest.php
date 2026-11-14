<?php

use Illuminate\Support\Facades\Gate;
use Wit3\FilamentRelationNavigator\RelationNavigator;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\User;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Policies\AuthorPolicy;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\AuthorResource;

beforeEach(function (): void {
    $this->actingAs(User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret']));

    Gate::policy(Author::class, AuthorPolicy::class);
});

it('denies the relation pages of a record the user cannot view', function (string $path): void {
    $secret = Author::create(['name' => 'Secret']);

    $this->get("/admin/{$path}/{$secret->getKey()}")->assertForbidden();
    $this->get("/admin/{$path}/{$secret->getKey()}/posts")->assertForbidden();
})->with([
    'a relation page class you wrote' => 'authors',
    'a generated relation page' => 'auto-authors',
]);

it('still opens the relation pages of a record the user can view', function (): void {
    $author = Author::create(['name' => 'Ada Lovelace']);

    $this->get("/admin/authors/{$author->getKey()}/posts")->assertOk();
});

it('lists only the record pages the user can open', function (): void {
    $readOnly = Author::create(['name' => 'Read only']);

    $keys = collect(RelationNavigator::getItems(AuthorResource::class, $readOnly))->pluck('key');

    expect($keys)->toContain('view', 'relations.posts')
        ->not->toContain('edit');

    $this->get("/admin/authors/{$readOnly->getKey()}/edit")->assertForbidden();
});

it('lists nothing for a record the user cannot view', function (): void {
    $secret = Author::create(['name' => 'Secret']);

    $keys = collect(RelationNavigator::getItems(AuthorResource::class, $secret))->pluck('key');

    expect($keys->filter(fn (string $key): bool => in_array($key, ['view', 'edit'], true) || str_starts_with($key, 'relations.')))->toBeEmpty();
});
