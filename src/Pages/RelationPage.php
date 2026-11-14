<?php

namespace Wit3\FilamentRelationNavigator\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Panel;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\RelationManagers\RelationManagerConfiguration;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Schema;
use Filament\Support\Components\Attributes\Unexposed;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Wit3\FilamentRelationNavigator\Concerns\HasRelationNavigator;
use Wit3\FilamentRelationNavigator\Concerns\HasRelationPages;
use Wit3\FilamentRelationNavigator\GeneratedRelationPages;
use Wit3\FilamentRelationNavigator\RelationNavigator;
use Wit3\FilamentRelationNavigator\RelationPageDefinition;

/**
 * One page per relation manager of the resource, at `/{record}/{slug}`.
 *
 * Extend it once per resource, then spread its routes into `getPages()`:
 *
 *     class UserRelationPage extends RelationPage
 *     {
 *         protected static string $resource = UserResource::class;
 *     }
 *
 *     public static function getPages(): array
 *     {
 *         return [
 *             'index' => ListUsers::route('/'),
 *             'view' => ViewUser::route('/{record}'),
 *             ...UserRelationPage::relationRoutes(),
 *         ];
 *     }
 *
 * The page renders the relation manager you already registered in
 * `getRelations()`: same table, actions, badges and authorization.
 */
abstract class RelationPage extends Page
{
    use HasRelationNavigator {
        HasRelationNavigator::getBreadcrumbs insteadof InteractsWithRecord;
    }
    use InteractsWithRecord {
        InteractsWithRecord::getBreadcrumbs as getRecordBreadcrumbs;
    }

    /**
     * Prefix of the page keys in `getPages()`: `relations.homes` gives the
     * route `...resources.users.relations.homes`.
     */
    public const PAGE_KEY_PREFIX = 'relations.';

    /**
     * Route default that tells the page which relation it is showing.
     */
    public const ROUTE_PARAMETER = 'relationPage';

    /**
     * Only these slugs get a page (null: every relation).
     *
     * @var array<string> | null
     */
    protected static ?array $relations = null;

    /**
     * These slugs never get a page.
     *
     * @var array<string>
     */
    protected static array $excludedRelations = [];

    /**
     * Resource page whose behaviour the relation managers see as
     * `$pageClass` (read-only on view pages, `canViewForRecord()`...).
     * Null: the `view` page, otherwise the `edit` page.
     */
    protected static ?string $contextPage = null;

    /**
     * @var array<class-string, bool>
     */
    private static array $resolvingReservedSlugs = [];

    #[Locked]
    public string $relationPage = '';

    public function mount(int|string $record, ?string $relationPage = null): void
    {
        $this->record = $this->resolveRecord($record);

        $this->relationPage = $relationPage ?? static::resolveRelationPageFromRoute() ?? '';

        $this->authorizeAccess();
    }

    public function hydrate(): void
    {
        $this->authorizeAccess();
    }

    protected function authorizeAccess(): void
    {
        $definition = $this->getRelationPageDefinition();

        abort_unless($definition !== null, 404);

        abort_unless(static::canAccess(['record' => $this->getRecord()]), 403);

        abort_unless($definition->canViewForRecord($this->getRecord(), static::getContextPageClass()), 403);
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    public static function canAccess(array $parameters = []): bool
    {
        $contextPageClass = static::getContextPageClass();

        $canAccess = ($contextPageClass !== static::class)
            ? $contextPageClass::canAccess($parameters)
            : parent::canAccess($parameters);

        if (! $canAccess) {
            return false;
        }

        $record = $parameters['record'] ?? null;

        return ! ($record instanceof Model) || static::canAccessRecord($record);
    }

    /**
     * The record check of the page this one stands for, which Filament runs
     * when that page mounts, not in canAccess(): the `view` policy for a view
     * page, `update` for an edit page.
     */
    public static function canAccessRecord(Model $record): bool
    {
        return RelationNavigator::canAccessRecordPage(static::getContextPageClass(), $record);
    }

    /**
     * The page registrations to spread into the resource's `getPages()`.
     *
     * @return array<string, PageRegistration>
     */
    public static function relationRoutes(): array
    {
        $registrations = [];

        foreach (array_keys(static::getRelationPageDefinitions()) as $slug) {
            $registrations[static::getPageKey($slug)] = new PageRegistration(
                page: static::class,
                route: function (Panel $panel) use ($slug): ?Route {
                    // A page of the resource with the same URL wins: never replace it.
                    if (self::isRouteTaken(static::getRelationRoutePath($slug))) {
                        return null;
                    }

                    return RouteFacade::get(static::getRelationRoutePath($slug), static::class)
                        ->defaults(static::ROUTE_PARAMETER, $slug)
                        ->middleware(static::getRouteMiddleware($panel))
                        ->withoutMiddleware(static::getWithoutRouteMiddleware($panel));
                },
            );
        }

        return $registrations;
    }

    /**
     * Whether a GET route already answers this path in the current route
     * group (resource slug, cluster, parent resource, tenant).
     */
    private static function isRouteTaken(string $path): bool
    {
        $groupStack = RouteFacade::getGroupStack();
        $domain = (string) (end($groupStack)['domain'] ?? '');
        $uri = trim(trim(RouteFacade::getLastGroupPrefix(), '/').'/'.trim($path, '/'), '/');

        return isset(RouteFacade::getRoutes()->getRoutesByMethod()['GET'][$domain.$uri]);
    }

    public static function getRelationRoutePath(string $slug): string
    {
        return "/{record}/{$slug}";
    }

    public static function getPageKey(string $slug): string
    {
        return static::PAGE_KEY_PREFIX.$slug;
    }

    /**
     * @return array<string, RelationPageDefinition>
     */
    public static function getRelationPageDefinitions(): array
    {
        $definitions = [];
        $reservedSlugs = static::getReservedSlugs();

        foreach (static::getResource()::getRelations() as $key => $manager) {
            $slug = static::getRelationPageSlug($key, $manager);

            if (blank($slug) || array_key_exists($slug, $definitions) || in_array($slug, $reservedSlugs, true)) {
                continue;
            }

            if (! static::shouldRegisterRelationPage($slug, $manager)) {
                continue;
            }

            $definitions[$slug] = new RelationPageDefinition($slug, $key, $manager);
        }

        return $definitions;
    }

    /**
     * Slug in the URL: the relation manager's own `getRelationPageSlug()`,
     * then the key in `getRelations()`, then the kebab-cased relationship
     * (or the group label).
     *
     * @param  class-string<RelationManager> | RelationGroup | RelationManagerConfiguration  $manager
     */
    public static function getRelationPageSlug(string|int $key, string|RelationGroup|RelationManagerConfiguration $manager): ?string
    {
        $managerClass = RelationNavigator::normalizeManagerClass($manager);

        if ($managerClass && method_exists($managerClass, 'getRelationPageSlug') && filled($slug = $managerClass::getRelationPageSlug())) {
            return $slug;
        }

        if (is_string($key) && filled($key)) {
            return $key;
        }

        if ($manager instanceof RelationGroup || $managerClass === null) {
            return Str::slug($manager instanceof RelationGroup ? $manager->getLabel() : (string) $key);
        }

        return Str::kebab($managerClass::getRelationshipName());
    }

    /**
     * @param  class-string<RelationManager> | RelationGroup | RelationManagerConfiguration  $manager
     */
    public static function shouldRegisterRelationPage(string $slug, string|RelationGroup|RelationManagerConfiguration $manager): bool
    {
        if ((static::$relations !== null) && (! in_array($slug, static::$relations, true))) {
            return false;
        }

        if (in_array($slug, static::$excludedRelations, true)) {
            return false;
        }

        $resource = static::getResource();

        if (method_exists($resource, 'hasRelationPage') && (! $resource::hasRelationPage($slug, $manager))) {
            return false;
        }

        $managerClass = RelationNavigator::normalizeManagerClass($manager);

        if ($managerClass && method_exists($managerClass, 'hasRelationPage')) {
            return (bool) $managerClass::hasRelationPage();
        }

        return static::includesRelationsByDefault();
    }

    /**
     * A page class you wrote includes every relation unless it opts out.
     * A generated one includes only the relation managers that opt in,
     * unless the resource uses {@see HasRelationPages}.
     */
    public static function includesRelationsByDefault(): bool
    {
        if (! GeneratedRelationPages::isGenerated(static::class)) {
            return true;
        }

        $resource = static::getResource();

        return method_exists($resource, 'hasRelationPages') && $resource::hasRelationPages();
    }

    /**
     * Slugs that would shadow the resource's own pages (`/{record}/edit`).
     *
     * @return array<string>
     */
    protected static function getReservedSlugs(): array
    {
        $resource = static::getResource();

        // getPages() of a resource with a page class you wrote calls
        // relationRoutes(), which lands back here.
        if (self::$resolvingReservedSlugs[$resource] ?? false) {
            return ['index', 'create', 'view', 'edit'];
        }

        self::$resolvingReservedSlugs[$resource] = true;

        try {
            $pageKeys = array_keys($resource::getPages());
        } finally {
            unset(self::$resolvingReservedSlugs[$resource]);
        }

        $reserved = [];

        foreach ($pageKeys as $pageKey) {
            $pageKey = (string) $pageKey;

            if (str_starts_with($pageKey, static::PAGE_KEY_PREFIX)) {
                continue;
            }

            $reserved[] = $pageKey;
            $reserved[] = Str::kebab($pageKey);
        }

        return $reserved;
    }

    /**
     * A generated page is not in `getPages()`: its name is its first route.
     */
    protected static function resolveResourcePageName(): string
    {
        foreach (static::getResource()::getPages() as $pageName => $pageRegistration) {
            if ($pageRegistration->getPage() === static::class) {
                return $pageName;
            }
        }

        return static::getPageKey((string) array_key_first(static::getRelationPageDefinitions()));
    }

    /**
     * @return class-string<Page>
     */
    public static function getContextPageClass(): string
    {
        $pages = static::getResource()::getPages();

        foreach (array_filter([static::$contextPage, 'view', 'edit']) as $pageKey) {
            if (array_key_exists($pageKey, $pages)) {
                return $pages[$pageKey]->getPage();
            }
        }

        return static::class;
    }

    protected static function resolveRelationPageFromRoute(): ?string
    {
        $route = request()->route();

        if (! $route) {
            return null;
        }

        $slug = $route->parameter(static::ROUTE_PARAMETER) ?? $route->defaults[static::ROUTE_PARAMETER] ?? null;

        if (filled($slug)) {
            return $slug;
        }

        $name = (string) $route->getName();
        $marker = '.'.static::PAGE_KEY_PREFIX;

        return str_contains($name, $marker) ? Str::afterLast($name, $marker) : null;
    }

    #[Unexposed]
    public function getRelationPageDefinition(): ?RelationPageDefinition
    {
        return static::getRelationPageDefinitions()[$this->relationPage] ?? null;
    }

    #[Unexposed]
    public function getRelationPageKey(): string
    {
        return static::getPageKey($this->relationPage);
    }

    #[Unexposed]
    public function getRelationTitle(): string
    {
        return $this->getRelationPageDefinition()?->getTitle($this->getRecord(), static::getContextPageClass()) ?? '';
    }

    #[Unexposed]
    public function getTitle(): string|Htmlable
    {
        if (filled(static::$title)) {
            return static::$title;
        }

        return $this->callRelationManagerHook('getRelationPageTitle') ?? $this->getRelationTitle();
    }

    /**
     * A static method of the page's relation manager, for this page only:
     * `getRelationPageTitle()`, `getRelationPageSubheading()` or
     * `getRelationPageBreadcrumb()`, given the owner record. Null when the
     * relation manager has none (or for a relation group).
     */
    protected function callRelationManagerHook(string $method): string|Htmlable|null
    {
        $managerClass = $this->getRelationPageDefinition()?->getManagerClass();

        if ($managerClass === null || ! method_exists($managerClass, $method)) {
            return null;
        }

        $value = $managerClass::$method($this->getRecord());

        return ($value instanceof Htmlable || is_string($value)) && filled($value) ? $value : null;
    }

    /**
     * The record the relation belongs to, under the relation title, unless
     * a header already shows it.
     */
    #[Unexposed]
    public function getSubheading(): string|Htmlable|null
    {
        if (filled($this->subheading ?? null)) {
            return $this->subheading;
        }

        $subheading = $this->callRelationManagerHook('getRelationPageSubheading');

        if ($subheading !== null) {
            return $subheading;
        }

        // The header already shows the record.
        if ($this->hasRelationPageHeader()) {
            return null;
        }

        return $this->getOwnerRecordTitle();
    }

    /**
     * The record's own name: the resource's `$recordTitleAttribute`, or its
     * `getRecordTitle()` when you override it without an attribute. Null
     * when the resource only knows the model label.
     */
    #[Unexposed]
    public function getOwnerRecordTitle(): string|Htmlable|null
    {
        $resource = static::getResource();
        $title = $resource::getRecordTitle($this->getRecord());

        if ($resource::hasRecordTitle()) {
            return $title;
        }

        if (blank($title) || in_array((string) $title, [$resource::getModelLabel(), $resource::getTitleCaseModelLabel()], true)) {
            return null;
        }

        return $title;
    }

    #[Unexposed]
    public function getBreadcrumb(): string
    {
        if (static::$breadcrumb !== null) {
            return static::$breadcrumb;
        }

        $breadcrumb = $this->callRelationManagerHook('getRelationPageBreadcrumb');

        return $breadcrumb instanceof Htmlable ? $breadcrumb->toHtml() : ($breadcrumb ?? $this->getRelationTitle());
    }

    /**
     * @return array<string | int, string | Htmlable>
     */
    #[Unexposed]
    public function getBreadcrumbs(): array
    {
        $breadcrumbs = $this->getRecordBreadcrumbs();

        // Filament adds the record only when the resource has a record title:
        // a relation page without it would lose the way back to its record.
        if (! static::getResource()::hasRecordTitle()) {
            $recordUrl = $this->getRecordBreadcrumbUrl();
            $last = array_pop($breadcrumbs) ?? $this->getBreadcrumb();

            $recordTitle = $this->getOwnerRecordTitle() ?? $this->getRecordTitle();

            if ($recordUrl) {
                $breadcrumbs[$recordUrl] = $recordTitle;
            } else {
                $breadcrumbs[] = $recordTitle;
            }

            $breadcrumbs[] = $last;
        }

        return $this->withRelationNavigator($breadcrumbs);
    }

    protected function getRecordBreadcrumbUrl(): ?string
    {
        $resource = static::getResource();
        $record = $this->getRecord();

        foreach (['view' => 'canView', 'edit' => 'canEdit'] as $pageKey => $ability) {
            if ($resource::hasPage($pageKey) && $resource::{$ability}($record)) {
                return $resource::getUrl($pageKey, ['record' => $record], shouldGuessMissingParameters: true);
            }
        }

        return null;
    }

    #[Unexposed]
    public function getRelationNavigatorActivePageKey(): ?string
    {
        return $this->getRelationPageKey();
    }

    /**
     * @return array<string>
     */
    #[Unexposed]
    public function getPageClasses(): array
    {
        return [
            'fi-resource-relation-page',
            'fi-resource-relation-page-'.$this->relationPage,
            "fi-resource-record-{$this->getRecord()->getKey()}",
        ];
    }

    #[Unexposed]
    public function content(Schema $schema): Schema
    {
        return $schema->components(array_filter([
            $this->hasRelationPageHeader() ? EmbeddedSchema::make('relationPageHeader') : null,
            $this->getRelationManagerComponent(),
        ]));
    }

    /**
     * Part of the record's view above the relation, from
     * `relationPageHeader(Schema $schema)` on the relation manager or, for
     * every relation page, on the resource:
     *
     *     public static function relationPageHeader(Schema $schema): Schema
     *     {
     *         return $schema->components([UserCard::make()]); // or static::infolist($schema)
     *     }
     */
    #[Unexposed]
    public function relationPageHeader(Schema $schema): Schema
    {
        $schema->record($this->getRecord());

        $owner = $this->getRelationPageHookOwner('relationPageHeader');

        return $owner ? $owner::relationPageHeader($schema) : $schema;
    }

    #[Unexposed]
    public function hasRelationPageHeader(): bool
    {
        return $this->getRelationPageHookOwner('relationPageHeader') !== null;
    }

    /**
     * `getRelationPageHeaderActions()` of the resource, then of the relation manager.
     *
     * @return array<Action | ActionGroup>
     */
    protected function getHeaderActions(): array
    {
        return $this->collectRelationPageHooks('getRelationPageHeaderActions');
    }

    /**
     * `getRelationPageHeaderWidgets()` of the resource, then of the relation
     * manager. Widgets receive the record as `$record`.
     *
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    protected function getHeaderWidgets(): array
    {
        return $this->collectRelationPageHooks('getRelationPageHeaderWidgets');
    }

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    protected function getFooterWidgets(): array
    {
        return $this->collectRelationPageHooks('getRelationPageFooterWidgets');
    }

    /**
     * Header actions act on the record the relation belongs to.
     */
    #[Unexposed]
    public function getDefaultActionRecord(Action $action): ?Model
    {
        if ($action->getTable()) {
            return null;
        }

        return $this->getRecord();
    }

    /**
     * The relation manager when it defines the hook, otherwise the resource.
     *
     * @return class-string | null
     */
    protected function getRelationPageHookOwner(string $method): ?string
    {
        $managerClass = $this->getRelationPageDefinition()?->getManagerClass();

        if ($managerClass && method_exists($managerClass, $method)) {
            return $managerClass;
        }

        $resource = static::getResource();

        return method_exists($resource, $method) ? $resource : null;
    }

    /**
     * @return array<mixed>
     */
    protected function collectRelationPageHooks(string $method): array
    {
        $resource = static::getResource();
        $managerClass = $this->getRelationPageDefinition()?->getManagerClass();

        return [
            ...(method_exists($resource, $method) ? $resource::$method() : []),
            ...(($managerClass && method_exists($managerClass, $method)) ? $managerClass::$method() : []),
        ];
    }

    #[Unexposed]
    public function getRelationManagerComponent(): Component
    {
        $definition = $this->getRelationPageDefinition();

        abort_if($definition === null, 404);

        $ownerRecord = $this->getRecord();
        $contextPageClass = static::getContextPageClass();
        $manager = $definition->manager;

        if ($manager instanceof RelationGroup) {
            $managers = $definition->getGroup($ownerRecord, $contextPageClass)->getManagers();

            return Group::make(collect($managers)
                ->map(fn (string|RelationManagerConfiguration $manager, string|int $key): Livewire => $this->makeRelationManagerLivewireComponent($manager, "{$this->relationPage}-{$key}"))
                ->values()
                ->all());
        }

        return $this->makeRelationManagerLivewireComponent($manager, $this->relationPage);
    }

    /**
     * @param  class-string<RelationManager> | RelationManagerConfiguration  $manager
     */
    protected function makeRelationManagerLivewireComponent(string|RelationManagerConfiguration $manager, string $key): Livewire
    {
        $managerClass = ($manager instanceof RelationManagerConfiguration) ? $manager->relationManager : $manager;

        $properties = ($manager instanceof RelationManagerConfiguration)
            ? [...$managerClass::getDefaultProperties(), ...$manager->getProperties()]
            : $managerClass::getDefaultProperties();

        return Livewire::make($managerClass, [
            ...$this->getRelationManagerLivewireData(),
            ...$properties,
        ])->key("{$managerClass}-{$key}");
    }

    /**
     * @return array<string, mixed>
     */
    protected function getRelationManagerLivewireData(): array
    {
        $data = [
            'ownerRecord' => $this->getRecord(),
            'pageClass' => static::getContextPageClass(),
        ];

        if ($activeLocale = (property_exists($this, 'activeLocale') ? $this->activeLocale : null)) {
            $data['activeLocale'] = $activeLocale;
        }

        return $data;
    }
}
