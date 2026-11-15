<?php

use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Wit3\FilamentRelationNavigator\RelationNavigator;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Team;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\User;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Policies\AuthorPolicy;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\AuthorResource;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\ListAuthors;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\ViewAuthor;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\PostsRelationManager;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\AutoAuthors\AutoAuthorResource;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Posts\PostResource;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\UnrelatedAuthors\Pages\ListUnrelatedAuthors;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\UnrelatedAuthors\UnrelatedAuthorResource;

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

describe('nested resources', function (): void {
    beforeEach(function (): void {
        // Keys interleaved between parents: 1 and 3 are Charles's, 2 is Ada's.
        $this->notes = $this->second->posts()->create(['title' => 'Notes']);
        $this->stray = $this->first->posts()->create(['title' => 'Stray']);
        $this->sketches = $this->second->posts()->create(['title' => 'Sketches']);
    });

    it('walks the records of the same parent only', function (string $path): void {
        $base = "/admin/authors/{$this->second->getKey()}/posts";

        $html = $this->get("{$base}/{$this->notes->getKey()}{$path}")->assertOk()->getContent();

        expect(neighbourUrl($html, 'previous'))->toBeNull()
            ->and(neighbourUrl($html, 'next'))->toEndWith("{$base}/{$this->sketches->getKey()}{$path}")
            ->and($html)->not->toContain('Stray');

        $html = $this->get("{$base}/{$this->sketches->getKey()}{$path}")->assertOk()->getContent();

        expect(neighbourUrl($html, 'previous'))->toEndWith("{$base}/{$this->notes->getKey()}{$path}")
            ->and(neighbourUrl($html, 'next'))->toBeNull();
    })->with([
        'view page' => '',
        'relation page' => '/revisions',
    ]);

    it('shows no buttons for the only record of its parent', function (): void {
        $this->get("/admin/authors/{$this->first->getKey()}/posts/{$this->stray->getKey()}")
            ->assertOk()
            ->assertDontSee('fi-relation-navigator-record-navigation', escape: false);
    });

    it('follows the relation manager table the user came from', function (): void {
        PostsRelationManager::$relatedResourceForTests = PostResource::class;

        // Titles sorted descending: Sketches, then Notes (the reverse of the key order).
        Livewire::test(PostsRelationManager::class, ['ownerRecord' => $this->second, 'pageClass' => ViewAuthor::class])
            ->call('sortTable', 'title', 'desc');

        expect(RelationNavigator::getRememberedListKeys(PostResource::class))
            ->toBe([(string) $this->sketches->getKey(), (string) $this->notes->getKey()]);

        $base = "/admin/authors/{$this->second->getKey()}/posts";
        $html = $this->get("{$base}/{$this->sketches->getKey()}")->assertOk()->getContent();

        expect(neighbourUrl($html, 'previous'))->toBeNull()
            ->and(neighbourUrl($html, 'next'))->toEndWith("{$base}/{$this->notes->getKey()}");
    });

    it('remembers no list from a relation manager whose related resource is not nested', function (): void {
        PostsRelationManager::$relatedResourceForTests = AutoAuthorResource::class;

        Livewire::test(PostsRelationManager::class, ['ownerRecord' => $this->second, 'pageClass' => ViewAuthor::class]);

        expect(RelationNavigator::getRememberedListKeys(AutoAuthorResource::class))->toBeNull();
    });

    it('keeps the parent list with the cookie session driver, which has room for one', function (): void {
        config()->set('session.driver', 'cookie');
        PostsRelationManager::$relatedResourceForTests = PostResource::class;

        $this->get('/admin/authors?sort=name:desc')->assertOk();

        // The view page of an author loads its posts table.
        Livewire::test(PostsRelationManager::class, ['ownerRecord' => $this->second, 'pageClass' => ViewAuthor::class]);

        expect(RelationNavigator::getRememberedListKeys(AuthorResource::class))
            ->toBe([(string) $this->third->getKey(), (string) $this->second->getKey(), (string) $this->first->getKey()])
            ->and(RelationNavigator::getRememberedListKeys(PostResource::class))->toBeNull();
    });

    it('remembers no list from a relation manager without a related resource', function (): void {
        Livewire::test(PostsRelationManager::class, ['ownerRecord' => $this->second, 'pageClass' => ViewAuthor::class]);

        expect(RelationNavigator::getRememberedListKeys(PostResource::class))->toBeNull();
    });
});

it('points previous to the right in right to left languages', function (): void {
    $chevron = fn (Heroicon $icon): string => \Filament\Support\generate_icon_html($icon)->toHtml();

    app()->setLocale('ar');
    $html = $this->get("/admin/authors/{$this->second->getKey()}")->assertOk()->getContent();
    $navigation = str($html)->after('fi-relation-navigator-record-navigation')->before('</div>')->toString();

    expect(strpos($navigation, $chevron(Heroicon::ChevronRight)))->toBeLessThan(strpos($navigation, $chevron(Heroicon::ChevronLeft)));
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

it('remembers no list for a resource whose pages never show previous and next', function (): void {
    // Turned on in the config for every resource: only the ones that use the
    // package keep a list, the others leave the session alone.
    Livewire::test(ListUnrelatedAuthors::class)->assertOk();

    expect(RelationNavigator::getRememberedListKeys(UnrelatedAuthorResource::class))->toBeNull()
        ->and(session()->get('filament-relation-navigator.lists', []))->toBe([]);

    Livewire::test(ListAuthors::class)->assertOk();

    expect(RelationNavigator::getRememberedListKeys(AuthorResource::class))->not->toBeNull();
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

    // About 30 UUID-like keys in 1 KB.
    expect(strlen(implode(',', $keys)))->toBeLessThanOrEqual(RelationNavigator::REMEMBERED_LIST_BYTES)
        ->and(count($keys))->toBeLessThan(100)->toBeGreaterThan(25);
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

it('keeps a single list with the cookie session driver', function (): void {
    config()->set('session.driver', 'cookie');

    session()->put('filament-relation-navigator.lists', ['other' => '1,2,3']);

    $this->get('/admin/authors')->assertOk();

    expect(session()->get('filament-relation-navigator.lists'))->toHaveCount(1)
        ->and(RelationNavigator::getRememberedListKeys(AuthorResource::class))->toHaveCount(3);
});

it('offers a neighbour the user can edit but not view', function (): void {
    Gate::policy(Author::class, AuthorPolicy::class);

    // "Edit only" can be edited, not viewed (see AuthorPolicy).
    $this->third->update(['name' => 'Edit only']);

    $html = $this->get("/admin/authors/{$this->second->getKey()}/edit")->assertOk()->getContent();

    expect(neighbourUrl($html, 'next'))->toEndWith("/admin/authors/{$this->third->getKey()}/edit");
});
