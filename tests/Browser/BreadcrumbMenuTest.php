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
        ->type('[data-relation-navigator-search]:visible', 'comm')
        ->assertSee('Published comments')
        ->assertDontSee('Activity')
        ->keys('[data-relation-navigator-search]:visible', 'Enter')
        ->assertPathIs("/admin/authors/{$id}/published-comments");
});

it('says so when nothing matches', function (): void {
    visit("/admin/authors/{$this->author->getKey()}/posts")
        ->click('.fi-relation-navigator-caret')
        ->type('[data-relation-navigator-search]:visible', 'zzz')
        ->assertSee('No pages found.');
});

it('checks the current page', function (): void {
    visit("/admin/authors/{$this->author->getKey()}/posts")
        ->click('.fi-relation-navigator-caret')
        ->assertPresent('.fi-relation-navigator-item.fi-active .fi-relation-navigator-check:visible');
});

it('moves through the menu with the keyboard', function (): void {
    $id = $this->author->getKey();

    visit("/admin/authors/{$id}/posts")
        ->click('.fi-relation-navigator-caret')
        ->keys('[data-relation-navigator-search]:visible', 'ArrowDown')
        ->assertScript('document.activeElement.dataset.relationNavigatorLabel', 'View')
        ->keys('[data-relation-navigator-label="View"]:visible', 'ArrowDown')
        ->assertScript('document.activeElement.dataset.relationNavigatorLabel', 'Edit')
        ->keys('[data-relation-navigator-label="Edit"]:visible', 'ArrowUp')
        ->keys('[data-relation-navigator-label="View"]:visible', 'ArrowUp')
        ->assertScript("document.activeElement.hasAttribute('data-relation-navigator-search')")
        ->keys('[data-relation-navigator-search]:visible', 'ArrowDown')
        ->keys('[data-relation-navigator-label="View"]:visible', 'ArrowDown')
        ->keys('[data-relation-navigator-label="Edit"]:visible', 'Enter')
        ->assertPathIs("/admin/authors/{$id}/edit");
});

it('shows the menu above the heading on a phone, and only there', function (): void {
    $id = $this->author->getKey();

    visit("/admin/authors/{$id}/posts")
        ->on()->mobile()
        ->assertVisible('.fi-relation-navigator-mobile-trigger')
        ->assertMissing('.fi-breadcrumbs')
        ->click('.fi-relation-navigator-mobile-trigger')
        ->click('a[data-relation-navigator-label="Published comments"]:visible')
        ->assertPathIs("/admin/authors/{$id}/published-comments");

    visit("/admin/authors/{$id}/posts")
        ->resize(1280, 800)
        ->assertMissing('.fi-relation-navigator-mobile-trigger')
        ->assertVisible('.fi-breadcrumbs');
});
