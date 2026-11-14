<?php

use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Team;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\User;

beforeEach(function (): void {
    $this->user = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret']);
    $this->actingAs($this->user);

    config()->set('filament-relation-navigator.redirect_relation_tabs', true);

    $this->author = Author::create(['name' => 'Ada Lovelace']);
    $this->author->posts()->create(['title' => 'Notes on the Analytical Engine']);
});

it('keeps the tabs by default', function (): void {
    config()->set('filament-relation-navigator.redirect_relation_tabs', null);

    $this->get("/admin/authors/{$this->author->getKey()}?relation=posts")->assertOk();
});

it('opens the relation page of a tab', function (string $relation, string $path): void {
    $id = $this->author->getKey();

    $this->get("/admin/authors/{$id}?relation={$relation}")
        ->assertRedirect("/admin/authors/{$id}/{$path}");

    $this->get("/admin/authors/{$id}/edit?relation={$relation}")
        ->assertRedirect("/admin/authors/{$id}/{$path}");
})->with([
    'a keyed relation manager' => ['posts', 'posts'],
    'a relation manager without a key' => ['0', 'published-comments'],
    'a relation group' => ['activity', 'activity'],
]);

it('stays on the page for a tab without a relation page', function (string $relation): void {
    $this->get("/admin/authors/{$this->author->getKey()}?relation={$relation}")->assertOk();
})->with([
    'a relation manager without a page' => ['1'],
    'a relation the record cannot see' => ['notes'],
    'an unknown tab' => ['nope'],
    'an empty tab' => [''],
]);

it('leaves the relation page and pages without a relation alone', function (): void {
    $this->get("/admin/authors/{$this->author->getKey()}/posts?relation=posts")->assertOk();
    $this->get("/admin/authors/{$this->author->getKey()}")->assertOk();
});

it('redirects inside the tenant', function (): void {
    $team = Team::create(['name' => 'Acme']);
    $this->user->teams()->attach($team);
    $this->author->update(['team_id' => $team->getKey()]);

    $base = "/app/{$team->getKey()}/tenant-authors/{$this->author->getKey()}";

    $this->get("{$base}?relation=posts")->assertRedirect("{$base}/posts");
});
