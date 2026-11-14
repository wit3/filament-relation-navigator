# الإعدادات

انشر ملف الإعدادات لتغيير القيم الافتراضية لكل الـ resources:

```bash
php artisan vendor:publish --tag=filament-relation-navigator-config
```

يستمر ملف الإعدادات المنشور في العمل عبر الإصدارات الثانوية (minor): لكل مفتاح قيمة افتراضية في الشيفرة، لذا لا تحتاج إلى إضافة المفاتيح الجديدة إلا عندما تريد تغييرها.

## الخيارات {#options}

يمكن ضبط معظم الخيارات أيضًا **لكل resource** (دالة static على الـ resource) أو **لكل صفحة أو relation manager**:

- **خيارات القائمة** (`menu.*` التي لها دالة، والتنقل بين السجلات، وإعادة توجيه التبويبات): تفوز الصفحة، ثم الـ resource، ثم الإعدادات.
- **«فتح كصفحة»** و**شارة الـ relation manager في القائمة**: لا تظهر إلا عندما تسمح بها كل المستويات؛ ويمكن لأي مستوى تعطيلها.
- **روابط البحث العام**: دالة الـ resource تحل محل الإعدادات لذلك الـ resource؛ ولا يستطيع الـ relation manager إلا استبعاد نفسه.
- **قائمة السجلات المحفوظة لزري السابق والتالي**: يقررها الـ resource أو الإعدادات فقط، لأن صفحة القائمة لا يمكنها سؤال صفحة السجل.
- `menu.presets` و`menu.search_from` و`menu.max_height` و`menu.separator` موجودة في الإعدادات فقط.

| المفتاح | الافتراضي | ماذا يفعل | المستويات الأخرى |
|---|---|---|---|
| `menu.preset` | `'github'` | شكل القائمة: `github` أو `simple` أو نمط من عندك أو اسم قالب | `getRelationNavigatorPreset()` |
| `menu.presets` | `simple`، `github` | أسماء الأنماط وقوالبها | |
| `menu.searchable` | `true` | حقل بحث أعلى القائمة | `isRelationNavigatorSearchable()` |
| `menu.search_from` | `0` | عرض حقل البحث ابتداء من هذا العدد من العناصر | |
| `menu.badges` | `'lazy'` | `'lazy'`: تُحمَّل الشارات عند فتح القائمة؛ `true`: مع الصفحة؛ `false`: بلا شارات | `hasRelationNavigatorBadges()`، `hasDeferredRelationNavigatorBadges()`؛ relation manager: `hasRelationNavigatorBadge()` |
| `menu.max_height` | `'24rem'` | ارتفاع القائمة قبل أن يظهر التمرير (طول CSS) | |
| `menu.separator` | `true` | صفحات السجل وصفحات العلاقات في كتلتين | |
| `menu.mobile` | `true` | القائمة فوق عنوان الصفحة في الشاشات الصغيرة | `hasRelationNavigatorMobileMenu()` |
| `menu.shortcut` | `'alt+shift+r'` | اختصار لوحة المفاتيح الذي يفتح القائمة؛ `null`: بلا اختصار | `getRelationNavigatorShortcut()` |
| `relation_manager_link` | `true` | إجراء «فتح كصفحة» على جداول الـ relation managers | الـ resource: `hasRelationPageLinks()`، الـ relation manager: `hasRelationPageLink()` |
| `relation_manager_action` | رابط مع سهم | شكل ذلك الإجراء: `style`، `label`، `icon`، `icon_position`، `color`، `size`، `new_tab` | `configureRelationPageActionUsing()`، `configureRelationPageAction()` |
| `global_search_actions` | `false` | روابط إلى صفحات العلاقات تحت نتائج البحث العام: `true` أو الحد الأقصى لكل نتيجة | الـ resource: `hasRelationPageGlobalSearchActions()`، الـ relation manager: `hasRelationPageGlobalSearchAction()` |
| `record_navigation` | `false` | زرا السجل السابق والتالي | `hasRelationNavigatorRecordNavigation()` |
| `record_navigation_follows_list` | `true` | يتبع زرا السابق والتالي قائمة السجلات التي جاء منها المستخدم | `shouldRelationNavigatorRecordNavigationFollowList()` |
| `redirect_relation_tabs` | `false` | `?relation=x` في صفحة العرض يفتح صفحة العلاقة | `shouldRedirectRelationNavigatorTabs()` |

دوال الصفحة دوال نسخة (instance) على صفحة سجل تستخدم `HasRelationNavigator`؛ وعلى الـ resource تكون الدالة نفسها static وتنطبق على كل صفحاته. انظر [الصفحة ثم resource ثم config](/ar/guide/menu-presets#الصفحة-ثم-resource-ثم-config).

## الملف كاملًا

كما يأتي مع الإصدار الحالي (التعليقات بالإنجليزية):

<<< ../../../config/filament-relation-navigator.php
