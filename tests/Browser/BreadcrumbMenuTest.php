<?php

use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\Author;
use Wit3\FilamentRelationNavigator\Tests\Fixtures\Models\User;

beforeEach(function (): void {
    // Run `vendor/bin/testbench filament:assets` once before these tests:
    // the menu needs Filament's scripts.
    pest()->browser()->timeout(8000);

    config()->set('filament-relation-navigator.menu.preset', 'github');
    config()->set('filament-relation-navigator.menu.searchable', true);

    $this->actingAs(User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'secret']));

    $this->author = Author::create(['name' => 'Ada Lovelace']);
    $this->author->posts()->create(['title' => 'Notes on the Analytical Engine']);
});

it('filters the menu as you type and opens the first match with Enter', function (): void {
    $id = $this->author->getKey();

    visit("/admin/authors/{$id}/posts")
        ->click('.fi-relation-navigator-caret')
        ->assertSee('Switch page')
        ->assertSee('Published comments')
        ->assertSee('Activity')
        ->type('[data-relation-navigator-search]', 'comm')
        ->assertSee('Published comments')
        ->assertDontSee('Activity')
        ->keys('[data-relation-navigator-search]', 'Enter')
        ->assertPathIs("/admin/authors/{$id}/published-comments");
});

it('says so when nothing matches', function (): void {
    visit("/admin/authors/{$this->author->getKey()}/posts")
        ->click('.fi-relation-navigator-caret')
        ->type('[data-relation-navigator-search]', 'zzz')
        ->assertSee('No pages found.');
});

it('checks the current page', function (): void {
    visit("/admin/authors/{$this->author->getKey()}/posts")
        ->click('.fi-relation-navigator-caret')
        ->assertPresent('.fi-relation-navigator-item.fi-active .fi-relation-navigator-check');
});
