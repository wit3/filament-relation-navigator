<?php

use Illuminate\Routing\CompiledRouteCollection;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Wit3\FilamentRelationNavigator\GeneratedRelationPages;
use Wit3\FilamentRelationNavigator\RelationNavigator;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\User;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\AuthorResource;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\Pages\AuthorRelationPage;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\OptedInCommentsRelationManager;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\Authors\RelationManagers\PostsRelationManager;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\AutoAuthors\AutoAuthorResource;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\AutoAuthors\Pages\ViewAutoAuthor;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\NoPagesAuthors\NoPagesAuthorResource;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\OptInAuthors\OptInAuthorResource;
use Wit3\FilamentRelationNavigator\Tests\TestCase;

beforeEach(function (): void {
    $this->actingAs(User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret']));

    $this->author = Author::create(['name' => 'Ada Lovelace']);
    $this->author->posts()->create(['title' => 'Notes on the Analytical Engine']);
    $this->author->comments()->create(['body' => 'A published comment', 'is_published' => true]);
});

it('gives every relation of a resource with HasRelationPages a page, without a page class', function (): void {
    $page = RelationNavigator::getRelationPageClass(AutoAuthorResource::class);

    expect($page)->toBe(GeneratedRelationPages::classFor(AutoAuthorResource::class))
        ->and(array_keys($page::getRelationPageDefinitions()))->toBe(['posts', 'notes', 'featured-posts'])
        ->and(Route::has('filament.admin.resources.auto-authors.relations.posts'))->toBeTrue()
        ->and(Route::has('filament.admin.resources.auto-authors.relations.notes'))->toBeTrue()
        ->and(Route::has('filament.admin.resources.auto-authors.relations.hidden-page'))->toBeFalse();

    $this->get("/admin/auto-authors/{$this->author->getKey()}/posts")
        ->assertOk()
        ->assertSeeLivewire(PostsRelationManager::class)
        ->assertSee("/admin/auto-authors/{$this->author->getKey()}", escape: false)
        ->assertSee('Ada Lovelace');

    $this->get("/admin/auto-authors/{$this->author->getKey()}/notes")->assertForbidden();
});

it('gives a page only to the relation managers that opt in', function (): void {
    $page = RelationNavigator::getRelationPageClass(OptInAuthorResource::class);

    expect(array_keys($page::getRelationPageDefinitions()))->toBe(['published-comments'])
        ->and(Route::has('filament.admin.resources.opt-in-authors.relations.published-comments'))->toBeTrue()
        ->and(Route::has('filament.admin.resources.opt-in-authors.relations.posts'))->toBeFalse();

    $this->get("/admin/opt-in-authors/{$this->author->getKey()}/published-comments")
        ->assertOk()
        ->assertSeeLivewire(OptedInCommentsRelationManager::class);
});

it('leaves resources that do not opt in alone', function (): void {
    expect(RelationNavigator::getRelationPageClass(NoPagesAuthorResource::class))->toBeNull()
        ->and(Route::has('filament.admin.resources.no-pages-authors.relations.posts'))->toBeFalse()
        ->and(is_file(GeneratedRelationPages::pathFor(GeneratedRelationPages::classFor(NoPagesAuthorResource::class))))->toBeFalse();
});

it('keeps the page class you wrote and does not generate another', function (): void {
    expect(RelationNavigator::getRelationPageClass(AuthorResource::class))->toBe(AuthorRelationPage::class)
        ->and(RelationNavigator::getGeneratedRelationPageClass(AuthorResource::class))->toBeNull();
});

it('registers the generated page in Livewire, so later requests find it by name', function (): void {
    $page = GeneratedRelationPages::classFor(AutoAuthorResource::class);

    Livewire::test(RelationNavigator::getLivewireComponentName($page), ['record' => $this->author->getKey(), 'relationPage' => 'posts'])
        ->assertOk()
        ->call('$refresh')
        ->assertOk()
        ->assertSet('relationPage', 'posts');

    expect($page::getRouteName())->toBe('filament.admin.resources.auto-authors.relations.posts');
});

it('writes the generated class again when its file is gone', function (): void {
    $page = GeneratedRelationPages::classFor(AutoAuthorResource::class);
    $path = GeneratedRelationPages::pathFor($page);

    @unlink($path);

    expect(GeneratedRelationPages::ensureFile($page, AutoAuthorResource::class))->toBe($path)
        ->and(file_get_contents($path))
        ->toContain('class AutoAuthorResourceRelationPage extends \Wit3\FilamentRelationNavigator\Pages\RelationPage')
        ->toContain('protected static string $resource = \\'.AutoAuthorResource::class.'::class;');
});

it('adds the generated pages to the view page menu', function (): void {
    $id = $this->author->getKey();

    $this->get("/admin/auto-authors/{$id}")
        ->assertOk()
        ->assertSee("/admin/auto-authors/{$id}/posts", escape: false)
        ->assertDontSee("/admin/auto-authors/{$id}/notes", escape: false);
});

it('names the record with the resource getRecordTitle() even without a title attribute', function (): void {
    $page = Livewire::test(
        RelationNavigator::getLivewireComponentName(GeneratedRelationPages::classFor(OptInAuthorResource::class)),
        ['record' => $this->author->getKey(), 'relationPage' => 'published-comments'],
    )->instance();

    $viewUrl = OptInAuthorResource::getUrl('view', ['record' => $this->author]);

    expect($page->getBreadcrumbs()[$viewUrl])->toBe('Author Ada Lovelace')
        ->and($page->getSubheading())->toBeNull(); // the header of the relation manager shows it
});

it('shows the header, the header actions and the widgets of the resource above the relation', function (): void {
    $this->get("/admin/auto-authors/{$this->author->getKey()}/posts")
        ->assertOk()
        ->assertSeeInOrder(['Header author', 'Ada Lovelace'])
        ->assertSee('Greet Ada Lovelace')
        ->assertSee('Posts written');

    $page = Livewire::test(
        RelationNavigator::getLivewireComponentName(GeneratedRelationPages::classFor(AutoAuthorResource::class)),
        ['record' => $this->author->getKey(), 'relationPage' => 'posts'],
    )->instance();

    expect($page->getSubheading())->toBeNull();
});

it('lets the relation manager replace the header of the resource', function (): void {
    $this->get("/admin/opt-in-authors/{$this->author->getKey()}/published-comments")
        ->assertOk()
        ->assertSee('Comments by')
        ->assertDontSee('Header author');
});

it('shows no header when nobody defines one', function (): void {
    $this->get("/admin/authors/{$this->author->getKey()}/posts")
        ->assertOk()
        ->assertDontSee('Header author')
        ->assertDontSee('Greet');
});

it('runs the header actions on the record the relation belongs to', function (): void {
    Livewire::test(
        RelationNavigator::getLivewireComponentName(GeneratedRelationPages::classFor(AutoAuthorResource::class)),
        ['record' => $this->author->getKey(), 'relationPage' => 'posts'],
    )->callAction('greet');

    expect($this->author->refresh()->name)->toBe('Ada Lovelace (greeted)');
});

it('shows the record under the title when there is no header', function (): void {
    $page = Livewire::test(AuthorRelationPage::class, ['record' => $this->author->getKey(), 'relationPage' => 'posts'])
        ->instance();

    expect($page->getSubheading())->toBe('Ada Lovelace');
});

it('writes the generated classes to the temporary directory when the storage is read-only', function (): void {
    $readOnly = sys_get_temp_dir().'/frn-read-only-'.uniqid();
    mkdir($readOnly.'/framework/cache', 0755, true);
    chmod($readOnly.'/framework/cache', 0555);

    $storage = app()->storagePath();
    GeneratedRelationPages::useCachePath(null);
    app()->useStoragePath($readOnly);

    $directory = rtrim(sys_get_temp_dir(), '/').'/filament-relation-navigator-'.posix_geteuid();

    try {
        expect(GeneratedRelationPages::pathFor('Some\\Class'))->toStartWith($directory.'/')
            // Only this user can write there.
            ->and(fileperms($directory) & 0777)->toBe(0700);

        // A directory others can write to is refused, not used.
        chmod($directory, 0777);

        expect(fn () => GeneratedRelationPages::pathFor('Some\\Class'))
            ->toThrow(RuntimeException::class, 'only this user can write');
    } finally {
        @chmod($directory, 0700);
        app()->useStoragePath($storage);
        GeneratedRelationPages::useCachePath(TestCase::generatedPagesPath());
        chmod($readOnly.'/framework/cache', 0755);
    }
});

it('replaces a generated class file that was changed, instead of including it', function (): void {
    $class = GeneratedRelationPages::classFor(AutoAuthorResource::class);
    $path = GeneratedRelationPages::pathFor($class);

    GeneratedRelationPages::ensureFile($class, AutoAuthorResource::class);
    $original = file_get_contents($path);

    file_put_contents($path, '<?php throw new Exception("tampered");');

    expect(GeneratedRelationPages::ensureFile($class, AutoAuthorResource::class))->toBe($path)
        ->and(file_get_contents($path))->toBe($original);
});

it('explains a cache directory it cannot write to', function (): void {
    $readOnly = sys_get_temp_dir().'/frn-read-only-'.uniqid();
    mkdir($readOnly, 0555, true);
    GeneratedRelationPages::useCachePath($readOnly);

    try {
        expect(fn () => GeneratedRelationPages::ensureFile('Wit3\\X\\YRelationPage', AutoAuthorResource::class))
            ->toThrow(RuntimeException::class, 'useCachePath()');
    } finally {
        GeneratedRelationPages::useCachePath(TestCase::generatedPagesPath());
        chmod($readOnly, 0755);
    }
});

it('passes the properties of a configured relation manager to its page', function (): void {
    $this->get("/admin/auto-authors/{$this->author->getKey()}/featured-posts")
        ->assertOk()
        ->assertSeeLivewire(PostsRelationManager::class);

    Livewire::test(PostsRelationManager::class, ['ownerRecord' => $this->author, 'pageClass' => ViewAutoAuthor::class, 'note' => 'Only the featured ones'])
        ->assertSee('Only the featured ones');

    $page = Livewire::test(
        RelationNavigator::getLivewireComponentName(GeneratedRelationPages::classFor(AutoAuthorResource::class)),
        ['record' => $this->author->getKey(), 'relationPage' => 'featured-posts'],
    );

    expect($page->html())->toContain('Only the featured ones');
});

it('works with cached routes', function (): void {
    // What `route:cache` stores and Laravel loads back: serialized, compiled
    // routes. (`artisan route:cache` itself would boot Testbench's skeleton
    // app, without this test's panels.)
    $router = app('router');
    $routes = $router->getRoutes();

    foreach ($routes as $route) {
        $route->prepareForSerialization();
    }

    $router->setCompiledRoutes(unserialize(serialize($routes->compile())));

    expect($router->getRoutes())->toBeInstanceOf(CompiledRouteCollection::class)
        ->and($router->getRoutes()->getByName('filament.admin.resources.auto-authors.relations.posts'))->not->toBeNull();

    $this->get("/admin/auto-authors/{$this->author->getKey()}/posts")
        ->assertOk()
        ->assertSeeLivewire(PostsRelationManager::class);

    $this->get("/admin/authors/{$this->author->getKey()}/posts")
        ->assertOk();
});
