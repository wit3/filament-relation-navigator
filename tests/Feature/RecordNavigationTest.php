<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Wit3\FilamentRelationNavigator\RelationNavigator;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Team;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\User;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Policies\AuthorPolicy;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\AuthorResource;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\ListAuthors;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\ViewAuthor;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\AutoAuthors\AutoAuthorResource;

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

describe('following the list', function (): void {
    it('follows the order of the list the user came from', function (): void {
        // Names sorted descending: Grace, Charles, Ada.
        $this->get('/admin/authors?sort=name:desc')->assertOk();

        $html = $this->get("/admin/authors/{$this->second->getKey()}/posts")->getContent();

        expect(neighbourUrl($html, 'previous'))->toEndWith("/admin/authors/{$this->third->getKey()}/posts")
            ->and(neighbourUrl($html, 'next'))->toEndWith("/admin/authors/{$this->first->getKey()}/posts");
    });

    it('stops at the ends of the list instead of jumping to the key order', function (): void {
        $this->get('/admin/authors?sort=name:desc')->assertOk();

        // Grace (third) is first in the list, Ada (first) is last.
        $html = $this->get("/admin/authors/{$this->third->getKey()}")->getContent();

        expect(neighbourUrl($html, 'previous'))->toBeNull()
            ->and(neighbourUrl($html, 'next'))->toEndWith("/admin/authors/{$this->second->getKey()}");

        $html = $this->get("/admin/authors/{$this->first->getKey()}")->getContent();

        expect(neighbourUrl($html, 'next'))->toBeNull();
    });

    it('keeps to the records the search left in the list', function (): void {
        $this->get('/admin/authors?search=Charles')->assertOk();

        // Alone in the list: no neighbours, so no buttons.
        $this->get("/admin/authors/{$this->second->getKey()}")
            ->assertOk()
            ->assertDontSee('fi-relation-navigator-record-navigation', escape: false);
    });

    it('uses the key order for a record that is not in the list', function (): void {
        $this->get('/admin/authors?search=Charles')->assertOk();

        $html = $this->get("/admin/authors/{$this->first->getKey()}")->getContent();

        expect(neighbourUrl($html, 'next'))->toEndWith("/admin/authors/{$this->second->getKey()}");
    });

    it('uses the key order when following the list is off', function (): void {
        config()->set('filament-relation-navigator.record_navigation_follows_list', false);

        $this->get('/admin/authors?sort=name:desc')->assertOk();

        $html = $this->get("/admin/authors/{$this->second->getKey()}")->getContent();

        expect(neighbourUrl($html, 'previous'))->toEndWith("/admin/authors/{$this->first->getKey()}")
            ->and(neighbourUrl($html, 'next'))->toEndWith("/admin/authors/{$this->third->getKey()}");
    });

    it('keeps one list per resource', function (): void {
        $this->get('/admin/authors?sort=name:desc')->assertOk();

        expect(RelationNavigator::getRememberedListKeys(AuthorResource::class))
            ->toBe([(string) $this->third->getKey(), (string) $this->second->getKey(), (string) $this->first->getKey()])
            ->and(RelationNavigator::getRememberedListKeys(AutoAuthorResource::class))->toBeNull();
    });
});

it('keeps the remembered list small enough for a cookie session', function (): void {
    $list = new class extends ListAuthors
    {
        public function isTableLoaded(): bool
        {
            return true;
        }

        public function getTableRecords(): Collection
        {
            // UUID-like keys.
            return collect(range(1, 100))->map(fn (int $i): Author => (new Author)->setKeyType('string')->forceFill(['id' => str_repeat('k', 32).$i]));
        }
    };

    RelationNavigator::rememberListRecords($list);

    $keys = RelationNavigator::getRememberedListKeys(AuthorResource::class);

    expect(strlen(serialize($keys)))->toBeLessThanOrEqual(RelationNavigator::REMEMBERED_LIST_BYTES)
        ->and(count($keys))->toBeLessThan(100)->toBeGreaterThan(15);
});

it('remembers the five most recent lists only', function (): void {
    foreach (range(1, 7) as $i) {
        // Seven different lists: one per tenant-like context is simulated
        // through the session directly.
        session()->put('filament-relation-navigator.lists', array_merge(
            session()->get('filament-relation-navigator.lists', []),
            ["list-{$i}" => ['1']],
        ));
    }

    $this->get('/admin/authors')->assertOk();

    $lists = session()->get('filament-relation-navigator.lists');

    expect($lists)->toHaveCount(RelationNavigator::REMEMBERED_LISTS)
        ->and(array_key_last($lists))->not->toStartWith('list-')
        ->and(RelationNavigator::getRememberedListKeys(AuthorResource::class))->not->toBeNull();
});
