<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Breadcrumb menu
    |--------------------------------------------------------------------------
    |
    | `preset` picks one of `presets`:
    |
    | - github (default): the last breadcrumb stays text, a caret next to it
    |           opens a panel with a heading and a check on the current page
    |           (like GitHub's "Switch repository").
    | - simple: the last breadcrumb itself opens the list.
    |
    | `searchable` adds a search field on top of the list, whatever the
    | preset; Enter opens the first match. For the menu of 1.2 and earlier,
    | set `'preset' => 'simple'` and `'searchable' => false`.
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

        'preset' => 'github',

        'presets' => [
            'simple' => 'filament-relation-navigator::menu.simple',
            'github' => 'filament-relation-navigator::menu.github',
        ],

        'searchable' => true,

        // With `searchable`, the search field appears from this many items.
        'search_from' => 0,

        // The relation managers' badges, as in their tabs. They run the same
        // queries as the tabs: 'lazy' loads them when the menu opens, false
        // turns them off.
        'badges' => true,

        'max_height' => '24rem',

        // The record pages and the relation pages in two blocks.
        'separator' => true,

        // Filament hides the breadcrumbs on small screens: show the menu
        // above the page heading there.
        'mobile' => true,

        // Opens the menu from anywhere on the page, outside text fields:
        // modifiers (alt, shift, ctrl, meta, or mod: ⌘ on a Mac, Ctrl
        // elsewhere) plus a letter, a digit or / . , ; Null turns it off.
        // A shortcut of the panel's global search is ignored, never doubled.
        'shortcut' => 'alt+shift+r',

    ],

    /*
    |--------------------------------------------------------------------------
    | Relation managers on the record pages
    |--------------------------------------------------------------------------
    |
    | An "Open as page" action in the header of the table of every relation
    | manager that has its own page, on the view and edit pages. Per resource:
    | hasRelationPageLinks(); per relation manager: hasRelationPageLink().
    |
    */

    'relation_manager_link' => true,

    /*
    |--------------------------------------------------------------------------
    | Global search
    |--------------------------------------------------------------------------
    |
    | Links to the record's relation pages under its global search results:
    | false, true, or the most links to show per result. Per resource:
    | hasRelationPageGlobalSearchActions(); per relation manager:
    | hasRelationPageGlobalSearchAction(). They run the relation managers'
    | canViewForRecord() for every result.
    |
    */

    'global_search_actions' => false,

    /*
    |--------------------------------------------------------------------------
    | Previous and next record
    |--------------------------------------------------------------------------
    |
    | Two buttons before the header actions of the pages with the menu: they
    | open the previous or the next record (by key) on the same page. Off by
    | default: they add a few queries per render. Per page, override
    | hasRelationNavigatorRecordNavigation(); to change the records,
    | getRelationNavigatorRecordNavigationQuery(). Not for nested resources.
    |
    */

    'record_navigation' => false,

    /*
    |--------------------------------------------------------------------------
    | Links to relation manager tabs
    |--------------------------------------------------------------------------
    |
    | A link to a tab of the view or edit page (`/customers/1?relation=bills`)
    | opens the relation page instead (`/customers/1/bills`). Handy once the
    | relation pages replace the tabs; off by default.
    |
    */

    'redirect_relation_tabs' => false,

];
