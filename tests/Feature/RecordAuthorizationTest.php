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

it('denies the relation pages of a record the user cannot view, wherever the view page is', function (string $path): void {
    $secret = Author::create(['name' => 'Secret']);

    $this->get("/admin/{$path}/{$secret->getKey()}/posts")->assertForbidden();

    $author = Author::create(['name' => 'Ada Lovelace']);

    $this->get("/admin/{$path}/{$author->getKey()}/posts")->assertOk();
})->with([
    'a view page under another key' => 'keyed-authors',
    'no view or edit page' => 'index-only-authors',
]);

it('answers 404 like the view page when the policy denies as not found', function (): void {
    $hidden = Author::create(['name' => 'Hidden']);

    $this->get("/admin/authors/{$hidden->getKey()}")->assertNotFound();
    $this->get("/admin/authors/{$hidden->getKey()}/posts")->assertNotFound();
    $this->get("/admin/auto-authors/{$hidden->getKey()}/posts")->assertNotFound();
});

it('lets the resource veto a relation page for a record', function (): void {
    $author = Author::create(['name' => 'Ada Lovelace']);

    AuthorResource::$options['canAccessRelationPage'] = fn ($record, string $slug): bool => $slug !== 'posts';

    try {
        $this->get("/admin/authors/{$author->getKey()}/posts")->assertForbidden();
        $this->get("/admin/authors/{$author->getKey()}/published-comments")->assertOk();

        expect(collect(RelationNavigator::getItems(AuthorResource::class, $author))->pluck('key'))
            ->not->toContain('relations.posts')
            ->toContain('relations.published-comments');
    } finally {
        AuthorResource::$options = [];
    }
});
