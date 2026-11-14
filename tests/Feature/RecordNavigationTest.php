<?php

use Illuminate\Support\Facades\Gate;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Team;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\User;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Policies\AuthorPolicy;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\ViewAuthor;

beforeEach(function (): void {
    $this->user = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret']);
    $this->actingAs($this->user);

    config()->set('filament-relation-navigator.record_navigation', true);

    $this->first = Author::create(['name' => 'Ada Lovelace']);
    $this->second = Author::create(['name' => 'Charles Babbage']);
    $this->third = Author::create(['name' => 'Grace Hopper']);
});

/**
 * The href of the previous or next button, or null when it is disabled.
 */
function neighbourUrl(string $html, string $direction): ?string
{
    $label = $direction === 'previous' ? 'Previous record' : 'Next record';

    preg_match('#<(a|button)\b(?=[^>]*aria-label="'.$label.'")[^>]*>#s', $html, $tag);

    expect($tag)->not->toBeEmpty("No [{$label}] button.");

    return preg_match('#href="([^"]+)"#', $tag[0], $href) ? html_entity_decode($href[1]) : null;
}

it('is off by default', function (): void {
    config()->set('filament-relation-navigator.record_navigation', null);

    $this->get("/admin/authors/{$this->second->getKey()}")
        ->assertOk()
        ->assertDontSee('fi-relation-navigator-record-navigation', escape: false);
});

it('opens the previous and next record on the same page', function (string $path): void {
    $html = $this->get("/admin/authors/{$this->second->getKey()}{$path}")->assertOk()->getContent();

    expect(neighbourUrl($html, 'previous'))->toEndWith("/admin/authors/{$this->first->getKey()}{$path}")
        ->and(neighbourUrl($html, 'next'))->toEndWith("/admin/authors/{$this->third->getKey()}{$path}")
        ->and($html)->toContain('Next record: Grace Hopper');
})->with([
    'view page' => '',
    'edit page' => '/edit',
    'relation page' => '/posts',
]);

it('disables the button at the first and the last record', function (): void {
    $html = $this->get("/admin/authors/{$this->first->getKey()}")->getContent();

    expect(neighbourUrl($html, 'previous'))->toBeNull()
        ->and(neighbourUrl($html, 'next'))->toEndWith("/admin/authors/{$this->second->getKey()}");

    $html = $this->get("/admin/authors/{$this->third->getKey()}")->getContent();

    expect(neighbourUrl($html, 'next'))->toBeNull();
});

it('falls back to the first page of a record that cannot open the same page', function (): void {
    $this->third->update(['name' => 'No comments']);

    $html = $this->get("/admin/authors/{$this->second->getKey()}/published-comments")->assertOk()->getContent();

    expect(neighbourUrl($html, 'previous'))->toEndWith("/admin/authors/{$this->first->getKey()}/published-comments")
        ->and(neighbourUrl($html, 'next'))->toEndWith("/admin/authors/{$this->third->getKey()}");
});

it('never leaves the tenant', function (): void {
    $team = Team::create(['name' => 'Acme']);
    $other = Team::create(['name' => 'Globex']);
    $this->user->teams()->attach($team);

    $mine = Author::create(['name' => 'Mine', 'team_id' => $team->getKey()]);
    $stranger = Author::create(['name' => 'Stranger', 'team_id' => $other->getKey()]);
    $alsoMine = Author::create(['name' => 'Also mine', 'team_id' => $team->getKey()]);

    $html = $this->get("/app/{$team->getKey()}/tenant-authors/{$mine->getKey()}")->assertOk()->getContent();

    expect(neighbourUrl($html, 'next'))->toEndWith("/app/{$team->getKey()}/tenant-authors/{$alsoMine->getKey()}")
        ->and(neighbourUrl($html, 'previous'))->toBeNull()
        ->and($html)->not->toContain('Stranger');
});

it('leaves nested resources alone', function (): void {
    $post = $this->second->posts()->create(['title' => 'Notes']);
    $this->second->posts()->create(['title' => 'More notes']);

    $this->get("/admin/authors/{$this->second->getKey()}/posts/{$post->getKey()}")
        ->assertOk()
        ->assertDontSee('fi-relation-navigator-record-navigation', escape: false);
});

it('can be turned on for one page only', function (): void {
    config()->set('filament-relation-navigator.record_navigation', false);

    $page = new class extends ViewAuthor
    {
        public function hasRelationNavigatorRecordNavigation(): bool
        {
            return true;
        }
    };

    $page->record = $this->second;

    expect($page->getRelationNavigatorRecordNeighbours()['next']?->label)->toBe('Grace Hopper');
});

it('skips the records the user cannot view, without showing their names', function (): void {
    Gate::policy(Author::class, AuthorPolicy::class);

    $this->third->update(['name' => 'Secret']);
    $fourth = Author::create(['name' => 'Katherine Johnson']);

    $html = $this->get("/admin/authors/{$this->second->getKey()}/posts")->assertOk()->getContent();

    expect(neighbourUrl($html, 'next'))->toEndWith("/admin/authors/{$fourth->getKey()}/posts")
        ->and($html)->not->toContain('Secret');

    $fourth->delete();

    $html = $this->get("/admin/authors/{$this->second->getKey()}/posts")->assertOk()->getContent();

    expect(neighbourUrl($html, 'next'))->toBeNull();

    $this->get("/admin/authors/{$this->third->getKey()}/posts")->assertForbidden();
});
