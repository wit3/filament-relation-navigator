<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Breadcrumb menu
    |--------------------------------------------------------------------------
    |
    | `preset` picks one of `presets`:
    |
    | - simple: the last breadcrumb itself opens the list.
    | - github: the last breadcrumb stays text, a caret next to it opens a
    |           panel with a heading and a check on the current page (like
    |           GitHub's "Switch repository").
    |
    | `searchable` adds a search field on top of the list, whatever the
    | preset; Enter opens the first match.
    |
    | Add your own preset by pointing a name to any view, or publish the
    | package views and change them:
    |
    |     php artisan vendor:publish --tag=filament-relation-navigator-views
    |
    | A page can override these with getRelationNavigatorPreset(),
    | isRelationNavigatorSearchable() and hasRelationNavigatorBadges().
    |
    */

    'menu' => [

        'preset' => 'simple',

        'presets' => [
            'simple' => 'filament-relation-navigator::menu.simple',
            'github' => 'filament-relation-navigator::menu.github',
        ],

        'searchable' => false,

        // With `searchable`, the search field appears from this many items.
        'search_from' => 0,

        // The relation managers' badges, as in their tabs. They run the same
        // queries as the tabs: turn them off if those are expensive.
        'badges' => true,

        'max_height' => '24rem',

        // The record pages and the relation pages in two blocks.
        'separator' => true,

        // Filament hides the breadcrumbs on small screens: show the menu
        // above the page heading there.
        'mobile' => true,

    ],

];
