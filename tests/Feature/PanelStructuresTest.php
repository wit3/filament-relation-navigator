<?php

use Illuminate\Support\Facades\Route;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Team;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\User;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\PostsRelationManager;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Posts\RelationManagers\RevisionsRelationManager;

beforeEach(function (): void {
    $this->user = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret']);
    $this->actingAs($this->user);

    $this->team = Team::create(['name' => 'Acme']);
    $this->otherTeam = Team::create(['name' => 'Globex']);
    $this->user->teams()->attach($this->team);

    $this->author = Author::create(['name' => 'Ada Lovelace', 'team_id' => $this->team->getKey()]);
    $this->post = $this->author->posts()->create(['title' => 'Notes on the Analytical Engine']);
    $this->post->revisions()->create(['note' => 'First draft']);

    $this->stranger = Author::create(['name' => 'Charles Babbage', 'team_id' => $this->otherTeam->getKey()]);
});

describe('nested resources', function (): void {
    it('serves the relation page of a child record under its parent', function (): void {
        $this->get("/admin/authors/{$this->author->getKey()}/posts/{$this->post->getKey()}/revisions")
            ->assertOk()
            ->assertSeeLivewire(RevisionsRelationManager::class)
            ->assertSee("/admin/authors/{$this->author->getKey()}", escape: false);
    });

    it('links the child relation pages from the child view page', function (): void {
        $base = "/admin/authors/{$this->author->getKey()}/posts/{$this->post->getKey()}";

        $this->get($base)
            ->assertOk()
            ->assertSee('fi-relation-navigator', escape: false)
            ->assertSee("{$base}/revisions", escape: false);
    });

    it('does not open a child under the wrong parent', function (): void {
        $this->get("/admin/authors/{$this->stranger->getKey()}/posts/{$this->post->getKey()}/revisions")
            ->assertNotFound();
    });
});

describe('clusters', function (): void {
    it('serves and links the relation pages of a clustered resource', function (): void {
        $id = $this->author->getKey();

        $this->get("/admin/settings/cluster-authors/{$id}/posts")
            ->assertOk()
            ->assertSeeLivewire(PostsRelationManager::class);

        $this->get("/admin/settings/cluster-authors/{$id}")
            ->assertOk()
            ->assertSee("/admin/settings/cluster-authors/{$id}/posts", escape: false);
    });
});

describe('tenancy and SPA mode', function (): void {
    it('serves the relation page inside the tenant', function (): void {
        $this->get("/app/{$this->team->getKey()}/tenant-authors/{$this->author->getKey()}/posts")
            ->assertOk()
            ->assertSeeLivewire(PostsRelationManager::class);
    });

    it('links the relation pages with the tenant and wire:navigate', function (): void {
        $url = "/app/{$this->team->getKey()}/tenant-authors/{$this->author->getKey()}";

        $response = $this->get($url)->assertOk();

        expect($response->getContent())
            ->toMatch('#<a[^>]+href="[^"]*'.preg_quote("{$url}/posts", '#').'"[^>]*wire:navigate#s');
    });

    it('keeps records of other tenants out', function (): void {
        $this->get("/app/{$this->team->getKey()}/tenant-authors/{$this->stranger->getKey()}/posts")
            ->assertNotFound();
    });
});

describe('resource configurations', function (): void {
    it('gives a configured resource its relation pages under its own slug', function (): void {
        $id = $this->author->getKey();

        expect(Route::has('filament.admin.resources.vip-authors.relations.posts'))->toBeTrue();

        $this->get("/admin/vip-authors/{$id}/posts")
            ->assertOk()
            ->assertSeeLivewire(PostsRelationManager::class);

        $this->get("/admin/vip-authors/{$id}")
            ->assertOk()
            ->assertSee("/admin/vip-authors/{$id}/posts", escape: false);
    });
});
