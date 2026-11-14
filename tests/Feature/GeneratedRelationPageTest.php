<?php

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
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\NoPagesAuthors\NoPagesAuthorResource;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Resources\OptInAuthors\OptInAuthorResource;

beforeEach(function (): void {
    $this->actingAs(User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret']));

    $this->author = Author::create(['name' => 'Ada Lovelace']);
    $this->author->posts()->create(['title' => 'Notes on the Analytical Engine']);
    $this->author->comments()->create(['body' => 'A published comment', 'is_published' => true]);
});

it('gives every relation of a resource with HasRelationPages a page, without a page class', function (): void {
    $page = RelationNavigator::getRelationPageClass(AutoAuthorResource::class);

    expect($page)->toBe(GeneratedRelationPages::classFor(AutoAuthorResource::class))
        ->and(array_keys($page::getRelationPageDefinitions()))->toBe(['posts', 'notes'])
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
