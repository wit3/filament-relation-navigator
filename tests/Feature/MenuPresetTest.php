<?php

use Filament\Facades\Filament;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Livewire\Livewire;
use Wit3\FilamentRelationNavigator\Concerns\HasRelationNavigator;
use Wit3\FilamentRelationNavigator\RelationNavigator;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\User;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\AuthorResource;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\ViewAuthor;

beforeEach(function (): void {
    $this->actingAs(User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret']));

    $this->author = Author::create(['name' => 'Ada Lovelace']);
    $this->author->posts()->create(['title' => 'Notes on the Analytical Engine']);
});

it('uses the github preset with the search field by default', function (): void {
    $this->get("/admin/authors/{$this->author->getKey()}")
        ->assertOk()
        ->assertSee('fi-relation-navigator-preset-github', escape: false)
        ->assertSee('x-model="search"', escape: false);
});

it('keeps the menu of 1.2 with the simple preset and no search', function (): void {
    config()->set('filament-relation-navigator.menu.preset', 'simple');
    config()->set('filament-relation-navigator.menu.searchable', false);

    $this->get("/admin/authors/{$this->author->getKey()}")
        ->assertOk()
        ->assertSee('fi-relation-navigator-preset-simple', escape: false)
        ->assertDontSee('fi-relation-navigator-caret', escape: false)
        ->assertDontSee('x-model="search"', escape: false);
});

it('falls back to the github preset with search when the config has no menu keys', function (): void {
    config()->set('filament-relation-navigator.menu', []);

    $this->get("/admin/authors/{$this->author->getKey()}")
        ->assertOk()
        ->assertSee('fi-relation-navigator-preset-github', escape: false)
        ->assertSee('x-model="search"', escape: false);
});

it('adds the search field to the simple preset', function (): void {
    config()->set('filament-relation-navigator.menu.preset', 'simple');
    config()->set('filament-relation-navigator.menu.searchable', true);
    config()->set('filament-relation-navigator.menu.mobile', false);

    $this->get("/admin/authors/{$this->author->getKey()}")
        ->assertOk()
        ->assertSee('fi-relation-navigator-preset-simple', escape: false)
        ->assertSee('x-model="search"', escape: false)
        ->assertSee('Find a page…')
        ->assertDontSee('fi-relation-navigator-heading', escape: false);
});

it('renders the github preset with a caret, a heading and the current page checked', function (): void {
    config()->set('filament-relation-navigator.menu.preset', 'github');
    config()->set('filament-relation-navigator.menu.searchable', false);

    $this->get("/admin/authors/{$this->author->getKey()}/posts")
        ->assertOk()
        ->assertSee('fi-relation-navigator-preset-github', escape: false)
        ->assertSee('fi-relation-navigator-caret', escape: false)
        ->assertSee('Switch page')
        ->assertSee('fi-relation-navigator-check', escape: false)
        ->assertDontSee('x-model="search"', escape: false);
});

it('adds the search field to the github preset, from the configured number of items', function (): void {
    config()->set('filament-relation-navigator.menu.preset', 'github');
    config()->set('filament-relation-navigator.menu.searchable', true);

    $this->get("/admin/authors/{$this->author->getKey()}/posts")
        ->assertSee('x-model="search"', escape: false);

    config()->set('filament-relation-navigator.menu.search_from', 50);

    $this->get("/admin/authors/{$this->author->getKey()}/posts")
        ->assertDontSee('x-model="search"', escape: false);
});

it('renders a preset of your own, from the presets or by view name', function (): void {
    View::addNamespace('frn-tests', __DIR__.'/../Fixtures/views');

    config()->set('filament-relation-navigator.menu.presets.mine', 'frn-tests::custom-menu');
    config()->set('filament-relation-navigator.menu.preset', 'mine');

    $this->get("/admin/authors/{$this->author->getKey()}/posts")
        ->assertOk()
        ->assertSee('my-custom-menu', escape: false)
        ->assertSee('Posts (6)');

    config()->set('filament-relation-navigator.menu.preset', 'frn-tests::custom-menu');

    $this->get("/admin/authors/{$this->author->getKey()}/posts")
        ->assertSee('my-custom-menu', escape: false);
});

it('creates a preset of your own from a package preset', function (): void {
    $path = resource_path('views/filament/relation-navigator/compact.blade.php');
    File::delete($path);

    try {
        $this->artisan('make:filament-relation-navigator-preset', ['name' => 'compact', '--from' => 'github'])
            ->expectsOutputToContain("'compact' => 'filament.relation-navigator.compact'")
            ->assertSuccessful();

        expect(File::get($path))->toContain('fi-relation-navigator-preset-github');

        config()->set('filament-relation-navigator.menu.presets.compact', 'filament.relation-navigator.compact');
        config()->set('filament-relation-navigator.menu.preset', 'compact');

        $this->get("/admin/authors/{$this->author->getKey()}/posts")
            ->assertOk()
            ->assertSee('Switch page');

        $this->artisan('make:filament-relation-navigator-preset', ['name' => 'nope', '--from' => 'missing'])
            ->assertFailed();
    } finally {
        File::delete($path);
    }
});

it('turns the badges off from the config', function (): void {
    config()->set('filament-relation-navigator.menu.badges', false);

    $page = Livewire::test(ViewAuthor::class, ['record' => $this->author->getKey()])->instance();

    expect(collect($page->getRelationNavigatorItems())->firstWhere('key', 'relations.posts')->badge)->toBeNull();
});

it('explains a preset that points to a missing view', function (): void {
    config()->set('filament-relation-navigator.menu.preset', 'does-not-exist');

    $this->withoutExceptionHandling();

    expect(fn () => $this->get("/admin/authors/{$this->author->getKey()}"))
        ->toThrow('does not exist. Check `filament-relation-navigator.menu.preset`');
});

it('shows the menu above the heading for small screens', function (): void {
    $id = $this->author->getKey();

    $this->get("/admin/authors/{$id}/posts")
        ->assertOk()
        ->assertSee('fi-relation-navigator-mobile', escape: false)
        ->assertSeeInOrder(['fi-relation-navigator-mobile-trigger', 'Posts', 'Switch page', "/admin/authors/{$id}/published-comments"], escape: false);

    config()->set('filament-relation-navigator.menu.mobile', false);

    $this->get("/admin/authors/{$id}/posts")
        ->assertDontSee('fi-relation-navigator-mobile', escape: false);
});

it('leaves pages without the trait alone on small screens', function (): void {
    $this->get("/admin/authors/{$this->author->getKey()}/history")
        ->assertOk()
        ->assertDontSee('fi-relation-navigator-mobile', escape: false);
});

it('separates the record pages from the relation pages', function (): void {
    config()->set('filament-relation-navigator.menu.preset', 'simple');
    $html = $this->get("/admin/authors/{$this->author->getKey()}")->getContent();
    $menu = str($html)->after('fi-relation-navigator-preset-simple')->before('</nav>');

    expect(substr_count((string) $menu, 'fi-dropdown-list"'))->toBe(2);

    config()->set('filament-relation-navigator.menu.separator', false);

    $html = $this->get("/admin/authors/{$this->author->getKey()}")->getContent();
    $menu = str($html)->after('fi-relation-navigator-preset-simple')->before('</nav>');

    expect(substr_count((string) $menu, 'fi-dropdown-list"'))->toBe(1);
});

it('tells relation pages from record pages', function (): void {
    $items = collect(RelationNavigator::getItems(
        AuthorResource::class,
        $this->author,
    ));

    expect($items->reject->isRelationPage()->pluck('key')->all())->toBe(['view', 'edit', 'stats'])
        ->and($items->filter->isRelationPage()->pluck('key')->first())->toBe('relations.posts');
});

it('speaks the supported languages', function (string $locale, string $heading): void {
    app()->setLocale($locale);

    expect(__('filament-relation-navigator::navigator.heading'))->toBe($heading);
})->with([
    ['en', 'Switch page'],
    ['it', 'Cambia pagina'],
    ['es', 'Cambiar de página'],
    ['de', 'Seite wechseln'],
    ['fr', 'Changer de page'],
    ['pt_BR', 'Trocar de página'],
    ['nl', 'Pagina wisselen'],
]);

it('shows the shortcut in a tooltip and binds it', function (): void {
    $html = $this->get("/admin/authors/{$this->author->getKey()}/posts?preset=github")->getContent();

    expect($html)
        ->toContain('x-tooltip="{ content: &#039;Switch page&#039;')
        ->toMatch('/⌥|\\\\u2325/u') // ⌥, raw or JSON-escaped depending on the Laravel version
        ->toContain('x-on:keydown.window="! $event.repeat')
        ->toContain('$event.code === &quot;KeyR&quot; &amp;&amp; $event.altKey === true &amp;&amp; $event.shiftKey === true')
        ->toContain('aria-keyshortcuts="Alt+Shift+R"')
        ->not->toContain('aria-label="Other pages of this record"'."\n            <!--[if BLOCK]>");
});

it('turns the shortcut off', function (): void {
    config()->set('filament-relation-navigator.menu.shortcut', null);

    $html = $this->get("/admin/authors/{$this->author->getKey()}/posts")->getContent();

    expect($html)
        ->not->toContain('x-on:keydown.window')
        ->not->toContain('aria-keyshortcuts')
        ->toContain('x-tooltip="{ content: &#039;Switch page&#039;');
});

it('spells shortcuts for a Mac and for everything else', function (): void {
    expect(RelationNavigator::getShortcutKeys('alt+shift+r'))
        ->toBe(['mac' => ['⌥', '⇧', 'R'], 'other' => ['Alt', 'Shift', 'R']])
        ->and(RelationNavigator::getShortcutKeys('mod+k'))
        ->toBe(['mac' => ['⌘', 'K'], 'other' => ['Ctrl', 'K']])
        ->and(RelationNavigator::getShortcutKeys(null))
        ->toBe(['mac' => [], 'other' => []]);
});

it('requires a page with a record', function (): void {
    $trait = new ReflectionClass(HasRelationNavigator::class);

    expect($trait->getMethod('getRecord')->isAbstract())->toBeTrue()
        ->and($trait->getMethod('hasRecord')->isAbstract())->toBeTrue();
});

it('never takes a shortcut of the global search', function (): void {
    Filament::getPanel('admin')->globalSearchKeyBindings(['mod+k', 'alt+shift+r']);

    $html = $this->get("/admin/authors/{$this->author->getKey()}/posts")->getContent();

    expect($html)->not->toContain('x-on:keydown.window')
        ->and(RelationNavigator::clashesWithFilament('command+k'))->toBeTrue()
        ->and(RelationNavigator::clashesWithFilament('ctrl+k'))->toBeTrue()
        ->and(RelationNavigator::clashesWithFilament('alt+shift+p'))->toBeFalse();
});

it('matches the physical key, with mod as ⌘ on a Mac and Ctrl elsewhere', function (): void {
    $condition = RelationNavigator::getShortcutCondition('mod+shift+p');

    expect($condition)
        ->toContain('$event.code === "KeyP"')
        ->toContain('$event.shiftKey === true')
        ->toContain('/Mac|iPhone|iPad/.test(navigator.userAgent) ? $event.metaKey === true')
        ->toContain('input, textarea, select')
        ->and(RelationNavigator::getShortcutCondition('alt+shift+7'))->toContain('"Digit7"')
        ->and(RelationNavigator::getShortcutCondition('alt+shift'))->toBeNull();
});

it('declares both variants of a mod shortcut to assistive technology', function (): void {
    config()->set('filament-relation-navigator.menu.shortcut', 'mod+shift+p');

    $this->get("/admin/authors/{$this->author->getKey()}/posts")
        ->assertSee('aria-keyshortcuts="Meta+Shift+P Control+Shift+P"', escape: false);
});

it('drops the separator line when the search hides the block above', function (): void {
    $this->get("/admin/authors/{$this->author->getKey()}")
        ->assertSee('x-bind:style="$el.previousElementSibling &amp;&amp; hasMatchesIn($el.previousElementSibling)', escape: false);
});

it('loads the badges when the menu opens with badges set to lazy', function (): void {
    config()->set('filament-relation-navigator.menu.badges', 'lazy');

    $this->author->posts()->create(['title' => 'Sketch of the Analytical Engine']);

    $html = $this->get("/admin/authors/{$this->author->getKey()}")->assertOk()->getContent();

    expect($html)
        ->toContain('fi-relation-navigator-badge-loading')
        ->toContain('x-init="true && new IntersectionObserver');

    $page = Livewire::test(ViewAuthor::class, ['record' => $this->author->getKey()]);

    expect($page->instance()->getRelationNavigatorItems())
        ->each(fn ($item) => $item->badge->toBeNull());

    $badges = $page->instance()->getRelationNavigatorBadges();

    expect($badges)->toHaveKey('relations.posts')
        ->and($badges['relations.posts']['badge'])->toBe('2')
        ->and($badges['relations.posts']['class'])->toContain('fi-color');
});

it('renders the badges with the page by default', function (): void {
    $html = $this->get("/admin/authors/{$this->author->getKey()}")->assertOk()->getContent();

    expect($html)
        ->not->toContain('fi-relation-navigator-badge-loading')
        ->toContain('x-init="false && new IntersectionObserver');
});

it('reads the options from the page, then the resource, then the config', function (): void {
    AuthorResource::$options = ['preset' => 'simple', 'recordNavigation' => true];
    Author::create(['name' => 'Charles Babbage']);

    try {
        $page = Livewire::test(ViewAuthor::class, ['record' => $this->author->getKey()])->instance();

        expect($page->getRelationNavigatorPreset())->toBe('simple')
            ->and($page->hasRelationNavigatorRecordNavigation())->toBeTrue()
            // Not set on the resource: the config.
            ->and($page->isRelationNavigatorSearchable())->toBeTrue();

        $this->get("/admin/authors/{$this->author->getKey()}")
            ->assertSee('fi-relation-navigator-preset-simple', escape: false)
            ->assertSee('fi-relation-navigator-record-navigation', escape: false);

        // Another resource keeps the config.
        $this->get("/admin/auto-authors/{$this->author->getKey()}")
            ->assertSee('fi-relation-navigator-preset-github', escape: false)
            ->assertDontSee('fi-relation-navigator-record-navigation', escape: false);
    } finally {
        AuthorResource::$options = [];
    }
});
