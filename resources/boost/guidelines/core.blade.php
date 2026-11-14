## Relation Navigator for Filament (wit3/filament-relation-navigator)

Gives every relation manager of a Filament v4/v5 resource its own page (`/customers/1/orders` instead of `/customers/1?relation=orders`) and turns the last breadcrumb of record pages into a menu of the record's pages. No page classes to generate: traits only.

### Turn it on

@verbatim
<code-snippet name="Relation pages for every relation manager of a resource" lang="php">
use Wit3\FilamentRelationNavigator\Concerns\HasRelationPages;

class CustomerResource extends Resource
{
    use HasRelationPages;
}
</code-snippet>

<code-snippet name="The breadcrumb menu on a record page (view, edit or custom)" lang="php">
use Wit3\FilamentRelationNavigator\Concerns\HasRelationNavigator;

class ViewCustomer extends ViewRecord
{
    use HasRelationNavigator;
}
</code-snippet>
@endverbatim

- Only for one relation manager instead of the whole resource: `use InteractsWithRelationPage;` on the relation manager (or `public static function hasRelationPage(): bool { return true; }`).
- Do not add the generated relation pages to `getPages()` and do not register routes: the package does it for every panel, tenancy, clusters and resource configurations included. Route names are `{resource route base}.relations.{slug}`: `CustomerResource::getUrl('relations.orders', ['record' => $customer])`.
- A record page is in the menu only if it uses `HasRelationNavigator`. If the page already overrides `getBreadcrumbs()`, return `$this->withRelationNavigator([...])`. A custom page that uses `InteractsWithRecord` itself needs `HasRelationNavigator::getBreadcrumbs insteadof InteractsWithRecord;`.
- Relation managers on a relation page receive the view page as `$pageClass`, so `isReadOnly()`, `canViewForRecord()` and badges behave as in the tab. Do not branch on the relation page class.

### Per relation manager

@verbatim
<code-snippet name="Relation manager options (with InteractsWithRelationPage)" lang="php">
protected static bool $hasRelationPage = false;      // no page for this one
protected static ?string $relationPageSlug = 'bills'; // custom slug
protected static bool $hasRelationPageLink = false;  // no "Open as page" action in its table
</code-snippet>
@endverbatim

### Options: page, then resource, then config

Every option is read from the record page (override the method), then from a static method with the same name on the resource (all its pages), then from `config/filament-relation-navigator.php`. Prefer the narrowest level that does the job.

| Method on the page / static on the resource | Config key | Default |
|---|---|---|
| `getRelationNavigatorPreset(): string` (`github`, `simple`, yours, a view name) | `menu.preset` | `github` |
| `isRelationNavigatorSearchable(): bool` | `menu.searchable` | `true` |
| `hasRelationNavigatorBadges(): bool` | `menu.badges` | `true` |
| `hasDeferredRelationNavigatorBadges(): bool` (badges loaded when the menu opens) | `menu.badges` = `'lazy'` | `false` |
| `getRelationNavigatorShortcut(): ?string` (`alt+shift+r`, `mod+…`; null = off) | `menu.shortcut` | `alt+shift+r` |
| `hasRelationNavigatorMobileMenu(): bool` | `menu.mobile` | `true` |
| `hasRelationNavigatorRecordNavigation(): bool` (previous/next record, same page) | `record_navigation` | `false` |
| `shouldRedirectRelationNavigatorTabs(): bool` (`?relation=x` opens the relation page) | `redirect_relation_tabs` | `false` |

The "Open as page" action of relation manager tables: config `relation_manager_link`, resource `hasRelationPageLinks(): bool`, relation manager `hasRelationPageLink(): bool`. All must allow it.

### Header of the relation pages

Define on the resource (all relation pages) or on a relation manager (its page only): `relationPageHeader(Schema $schema): Schema`, `getRelationPageHeaderActions(): array`, `getRelationPageHeaderWidgets(): array`, `getRelationPageFooterWidgets(): array`. They act on the owner record. Methods of the view page class are not reachable from there: move shared pieces to the resource or to a class.

### Don'ts

- Don't create a `RelationPage` subclass unless you need to override its methods; then `php artisan make:filament-relation-navigator` and spread `...CustomerRelationPage::relationRoutes()` into `getPages()`.
- Don't put Blade `@@if` inside HTML tag attributes in published preset views: Livewire adds comment markers there. Build attributes in PHP.
- After changing the package CSS (or upgrading), run `php artisan filament:assets`.
