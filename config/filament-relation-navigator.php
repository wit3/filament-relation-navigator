<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Breadcrumb menu
    |--------------------------------------------------------------------------
    |
    | `preset` picks one of `presets`:
    |
    | - dropdown:   the last breadcrumb opens a plain list.
    | - searchable: the last breadcrumb stays text, a caret next to it opens a
    |               panel with a heading, a search field and a check on the
    |               current page (like GitHub's "Switch repository").
    |
    | Add your own preset by pointing a name to any view, or publish the
    | package views and change them:
    |
    |     php artisan vendor:publish --tag=filament-relation-navigator-views
    |
    | A page can choose its preset with getRelationNavigatorPreset().
    |
    */

    'menu' => [

        'preset' => 'dropdown',

        'presets' => [
            'dropdown' => 'filament-relation-navigator::menu.dropdown',
            'searchable' => 'filament-relation-navigator::menu.searchable',
        ],

        // The relation managers' badges, as in their tabs. They run the same
        // queries as the tabs: turn them off if those are expensive.
        'badges' => true,

        'max_height' => '24rem',

        // Searchable preset: the search field appears from this many items.
        'search_from' => 0,

    ],

];
