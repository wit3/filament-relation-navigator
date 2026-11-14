# Configuration

Publish the config file to change the defaults for every resource:

```bash
php artisan vendor:publish --tag=filament-relation-navigator-config
```

Every option of the menu can also be set on a resource (a static method) or on a page (a method): see [Page, resource or config](/guide/menu-presets#page-resource-or-config). The "Open as page" action can be turned off per resource and per relation manager: see [Open as page](/guide/open-as-page).

This is the whole file, as shipped with the current version:

<<< ../../config/filament-relation-navigator.php
