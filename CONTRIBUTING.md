# Contributing

Thanks for helping. Issues, ideas and pull requests are welcome.

## Before you start

- **Bugs**: open an issue with the code that reproduces it (resource,
  relation manager, page) and your Filament, Laravel and PHP versions.
- **Features and questions**: start a
  [discussion](https://github.com/wit3/filament-relation-navigator/discussions)
  first, so we can agree on the approach before you write code.
- **Security problems**: see [SECURITY.md](SECURITY.md), never a public issue.

## Development

```bash
composer install
composer test        # the test suite
composer analyse     # PHPStan, level 8
composer format      # Pint
```

Browser tests (the menu's JavaScript) need Playwright:

```bash
npm install && npx playwright install chromium
vendor/bin/testbench filament:assets
composer test:browser
```

## Pull requests

- One change per pull request, with tests.
- Keep backwards compatibility: this package follows
  [Semantic Versioning](https://semver.org). A change that breaks the public
  API (traits, public and protected methods, config keys, route names, view
  variables, commands) waits for the next major version.
- Add a line to the `Unreleased` part of [CHANGELOG.md](CHANGELOG.md).
- The CI must pass on Filament 4 and 5.
