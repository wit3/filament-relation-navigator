# مرجع الـ API

كل ما يلي API عامة، يغطيها [الإصدار الدلالي](/ar/reference/upgrade): لا تتغير بشكل يكسر التوافق ضمن 1.x. كل ما ليس مذكورًا هنا داخلي.

## الـ traits

| الـ trait | أين يُستخدم | ماذا يفعل |
|---|---|---|
| `Concerns\HasRelationPages` | resource | يحصل كل relation manager على صفحة خاصة به |
| `Concerns\InteractsWithRelationPage` | relation manager | يحصل هذا الـ relation manager على صفحة؛ ويقرأ الخصائص الثابتة الاختيارية أدناه |
| `Concerns\HasRelationNavigator` | صفحة سجل (عرض، تعديل، مخصصة) | قائمة مسار التنقل، وتُدرج الصفحة فيها |

كلها في مساحة الأسماء `Wit3\FilamentRelationNavigator`.

## على resource

كلها static، وكلها اختيارية.

| الدالة | الغرض |
|---|---|
| `hasRelationPage(string $slug): bool` | استبعاد علاقة |
| `canAccessRelationPage(Model $record, string $slug): bool` | منع صفحة علاقة لسجل (الصفحات، القائمة، البحث العام، إعادة التوجيه) |
| `relationPageHeader(Schema $schema): Schema` | الترويسة فوق العلاقة في كل صفحات العلاقات |
| `getRelationPageHeaderActions(): array` | إجراءات الترويسة في صفحات العلاقات |
| `getRelationPageHeaderWidgets(): array`، `getRelationPageFooterWidgets(): array` | الـ widgets في صفحات العلاقات (تتلقى `$record`) |
| `hasRelationPageLinks(): bool` | «فتح كصفحة» على الـ relation managers التابعة له |
| `configureRelationPageAction(Action $action, RelationManager $manager): Action` | تغيير ذلك الإجراء لهذا الـ resource |
| `hasRelationPageGlobalSearchActions(): bool` | روابط البحث العام لهذا الـ resource |
| أي [دالة خيار](/ar/reference/configuration#options)، بصيغة static | الخيار لكل صفحات الـ resource |

## على relation manager

كلها static، وكلها اختيارية. تحتاج الخصائص إلى `InteractsWithRelationPage`؛ أما الدوال فتعمل من دونه.

| الخاصية أو الدالة | الغرض |
|---|---|
| `$hasRelationPage`، `hasRelationPage(): bool` | صفحة لهذا الـ relation manager أو لا |
| `$relationPageSlug`، `getRelationPageSlug(): ?string` | الـ slug الخاص بصفحته |
| `$hasRelationPageLink`، `hasRelationPageLink(): bool` | «فتح كصفحة» على جدوله |
| `$hasRelationPageGlobalSearchAction`، `hasRelationPageGlobalSearchAction(): bool` | رابطه تحت نتائج البحث العام |
| `$hasRelationNavigatorBadge`، `hasRelationNavigatorBadge(): bool` | شارته في القائمة (يحتفظ التبويب بشارته) |
| `getRelationPageTitle(Model $ownerRecord)` | عنوان صفحته؛ `null` يبقي القيمة الافتراضية |
| `getRelationPageSubheading(Model $ownerRecord)` | العنوان الفرعي لصفحته |
| `getRelationPageBreadcrumb(Model $ownerRecord): ?string` | آخر عنصر في مسار التنقل لصفحته |
| `relationPageHeader()`، `getRelationPageHeaderActions()`، `getRelationPageHeaderWidgets()`، `getRelationPageFooterWidgets()` | كما على الـ resource، لصفحته فقط |
| `configureRelationPageAction(Action $action, RelationManager $manager): Action` | تغيير إجراء «فتح كصفحة» الخاص به |

دالة Filament نفسها `isBadgeLazyLoaded()` (أو `$isBadgeLazyLoaded`) تجعل شارته في القائمة مؤجلة التحميل أيضًا.

## على صفحة سجل (`HasRelationNavigator`)

أعد تعريف أي منها على الصفحة:

| الدالة | الغرض |
|---|---|
| [دوال الخيارات](/ar/reference/configuration#options) | خيارات هذه الصفحة |
| `getRelationNavigatorRecordNavigationQuery(): ?Builder` | السجلات التي يتنقل بينها زرا السابق والتالي |
| `withRelationNavigator(array $breadcrumbs): array` | إضافة القائمة إلى عناصر مسار تنقل تبنيها بنفسك |

## صنف صفحة علاقة خاص بك

وسّع `Pages\RelationPage` (أو شغّل `php artisan make:filament-relation-navigator CustomerResource`)، وأضف `...CustomerRelationPage::relationRoutes()` إلى `getPages()`.

| العضو | الغرض |
|---|---|
| `protected static ?array $relations` | هذه الـ slugs فقط |
| `protected static array $excludedRelations` | ليس هذه الـ slugs أبدًا |
| `protected static ?string $contextPage` | مفتاح الصفحة التي يقوم مقامها (الافتراضي: صفحة العرض، وإلا فصفحة التعديل) |
| `$title`، `$subheading`، `$breadcrumb`، وكل دوال صفحات Filament | كما في أي صفحة Filament |

## الدوال المساعدة

الدوال الثابتة في `RelationNavigator`:

| الدالة | الغرض |
|---|---|
| `getItems(string $resource, Model $record, ...)` | عناصر القائمة لسجل |
| `getRecordSubNavigation(Page $page): array` | العناصر نفسها بصيغة التنقل الفرعي في Filament |
| `getRelationManagerPageUrl(RelationManager $manager): ?string` | صفحة relation manager، لأزرار من عندك |
| `getGlobalSearchResultActions(string $resource, Model $record): array` | روابط البحث العام، لدمجها مع روابطك |
| `configureRelationPageActionUsing(Closure $callback): void` | تغيير كل إجراءات «فتح كصفحة» |
| `makeRelationPageAction(RelationManager $manager): Action` | بناء ذلك الإجراء |
| `getRelationPageClass(string $resource): ?string` | صنف صفحة العلاقات لـ resource |
| `RELATION_PAGE_ACTION` | اسم إجراء «فتح كصفحة» |

`RelationNavigatorItem` (في `$items`): `key`، `label`، `url`، `icon`، `badge`، `badgeColor`، `isActive`، `isBadgeDeferred`، `isRelationPage()`.

## المسارات والقوالب والأوامر

- أسماء المسارات: `{resource route base}.relations.{slug}`، لاستخدامها في `getUrl('relations.orders', [...])`.
- القوالب: `filament-relation-navigator::menu.github`، `menu.simple`، `menu.mobile`، `menu.partials.list`، `menu.partials.trigger-attributes`. متغيراتها مذكورة في [نمط خاص بك](/ar/guide/menu-presets#نمط-خاص-بك).
- الأوامر: `make:filament-relation-navigator {resource}`، `make:filament-relation-navigator-preset {name} --from=github|simple`.
- الترجمات: `filament-relation-navigator::navigator.*`.
