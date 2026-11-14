# AGENTS.md

Guidance for AI coding agents (Codex, Claude Code, Copilot, Cursor...) working on this repository. For using the package in an app, see `resources/boost/skills/relation-navigator-development/SKILL.md` instead.

## What this is

`wit3/filament-relation-navigator`: a Filament 4/5 plugin that gives every relation manager its own page and route, and turns the last breadcrumb of record pages into a menu. Public and free (MIT). PHP 8.2+, Laravel 12-13.

## Layout

- `src/Pages/RelationPage.php`: the relation page. The package writes one subclass per resource on demand (`src/GeneratedRelationPages.php`, like Laravel's real-time facades).
- `src/RelationNavigator.php`: routes, menu items, shortcut, the "Open as page" table action, record checks.
- `src/Concerns/`: the public traits (`HasRelationPages`, `HasRelationNavigator`, `InteractsWithRelationPage`).
- `resources/views/menu/`: the `github` and `simple` presets and their partials.
- `resources/boost/`: Laravel Boost guidelines and skill, shipped to users.
- `tests/`: Pest. `tests/Fixtures` is a small app (two panels, tenancy, clusters, nested and configured resources). `tests/Browser` uses Pest's browser plugin (Playwright).
- `docs/`: the VitePress site. Its pages include README sections by region.

## Commands

```bash
composer test           # Pest (feature tests)
composer test:browser   # browser tests: needs `npm install && npx playwright install chromium` and `vendor/bin/testbench filament:assets`
composer analyse        # PHPStan level 8, no baseline: fix the code, never silence errors
composer format         # Pint
cd docs && npm ci && npm run build   # documentation site
```

CI runs the suite on Filament 4 and 5, Laravel 12 and 13, PHP 8.2-8.5 and the lowest allowed versions (Filament 4.13, Livewire 3.8). Anything you use must exist there.

## Rules

- **Semantic versioning.** Public API = traits and their public/protected methods, `RelationNavigator` public methods, config keys, route names (`relations.{slug}`), view variables, commands. No breaking change outside a major: deprecate instead (`@deprecated`, CHANGELOG) and keep it working. New config keys always need a default in the code, so older published configs keep working.
- **Every feature can be turned off** globally (config), per resource (a static method with the same name as the page method) and per page or relation manager. Follow the existing `resolveRelationNavigatorOption()` pattern.
- **CHANGELOG.md**: every change under `## Unreleased` (Added / Changed / Deprecated / Fixed / Security). A release moves it under `## x.y.z - date`; pushing the `vx.y.z` tag publishes the GitHub release from that section.
- **README.md is the documentation source.** Its `<!-- #region name -->` markers feed the docs site: keep them balanced and keep the names stable. Use absolute URLs for links to repository files.
- **Commits and texts in English**, imperative mood ("Add …", "Fix …").
- **Blade/Livewire:** no Blade `@if` inside an HTML tag's attributes in views rendered by Livewire (it injects comment markers): build attribute strings in PHP.
- **Relation managers on a relation page receive the view page as `$pageClass`.** Tell pages apart by URL (see `RelationNavigator::isOriginalUrl()`, which also works on Livewire requests), never by the page class.
- **Authorization:** Filament checks the record's `view`/`update` policies when view/edit pages mount, not in `canAccess()`. Anything that lists or opens record pages must check `RelationNavigator::canAccessRecordPage()`.
- **Tests first** for behaviour changes; add a browser test for anything that depends on JavaScript.
- GitHub Actions are pinned to commit SHAs with the version in a comment; Dependabot updates them.
- **Security advisories:** draft first, publish only after the fixed version is on Packagist. The docs build reads published advisories (`docs/scripts/advisories.mjs`, with `docs/advisories.json` as the committed fallback: update it too) and shows a banner on affected versions; older minor versions are rebuilt from their tags by `docs/scripts/build-versions.mjs`.
