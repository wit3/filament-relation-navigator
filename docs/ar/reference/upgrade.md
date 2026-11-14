# دليل الترقية

تتبع الحزمة [الإصدار الدلالي](https://semver.org): الإصدارات الثانوية (minor) والتصحيحية (patch) لا تكسر شيفرتك أبدًا. تذكر هذه الصفحة ما قد ترغب في مراجعته عند التحديث. وفي [سجل التغييرات](/ar/reference/changelog) كل تغيير.

```bash
composer update wit3/filament-relation-navigator
php artisan filament:assets
```

## إلى 1.7

لا شيء لتغييره. عندما يكون التنقل بين السجلات مفعلًا، **تعرض الـ resources المتداخلة زري السابق والتالي** أيضًا، ضمن السجل الأب نفسه، بترتيب الـ relation manager الخاص بالأب عندما يكون الـ resource المتداخل هو الـ related resource الخاص به (`$relatedResource`). لإخفائهما في resource متداخل، أعد `false` من `hasRelationNavigatorRecordNavigation()` على صفحاته أو على الـ resource.

## إلى 1.6

لا شيء لتغييره.

- منذ 1.6.1، أصبحت البنية الداخلية للحزمة معلمة بـ `@internal` (انظر سجل التغييرات): إذا كنت تستدعي `RelationNavigator::optsIn()` أو `registerRoutes()` أو دوال مساعدة مشابهة، فانتقل إلى ما يذكره [مرجع الـ API](/ar/reference/api).
- Filament 4.13 هو أدنى إصدار مسموح به منذ 1.6.1.

- **تُحمَّل شارات القائمة عند فتح القائمة**، لا مع الصفحة. للسلوك القديم: `'badges' => true`. قوالب القائمة المنشورة قبل 1.3، وأنماطك الخاصة، تستمر في الحصول عليها مع الصفحة.
- لإجراء «فتح كصفحة» خيارات جديدة (`relation_manager_action`) ونقاط تخصيص (hooks) جديدة: انظر [فتح كصفحة](/ar/guide/open-as-page).

## إلى 1.5

لا شيء لتغييره. هناك سلوكان مختلفان:

- **يتبع زرا السابق والتالي قائمة السجلات التي جئت منها** (الترتيب، الفلاتر، البحث، التبويب) بدلًا من ترتيب المفاتيح، عندما يكون التنقل بين السجلات مفعلًا. للإبقاء على ترتيب المفاتيح: `'record_navigation_follows_list' => false`.
- **«فتح كصفحة»** يفتح صفحة الـ relation manager الخاصة قبل صفحة مجموعة العلاقات التي ينتمي إليها أيضًا.

إذا كنت قد نشرت قوالب الحزمة، فانشر `menu/partials/list.blade.php` مرة أخرى: الـ relation managers التي تكون شارة تبويبها مؤجلة التحميل (lazy) تحمّلها الآن بالطريقة نفسها في القائمة أيضًا.

## إلى 1.4

لا شيء لتغييره.

- رابط **«فتح كصفحة»** أصبح الآن إجراء في ترويسة جدول الـ relation manager بدلًا من رابط فوقه. إذا أردت تعطيله في مكان ما، فانظر [فتح كصفحة](/ar/guide/open-as-page): الإعدادات، أو الـ resource، أو الـ relation manager.
- `RelationNavigator::renderRelationManagerLink()` مهملة (deprecated): لم تعد الحزمة تستخدمها. ما زالت تعمل لـ render hook من عندك، وستُزال في 2.0.
- يمكن الآن ضبط كل خيار من خيارات القائمة على resource أيضًا: انظر [الصفحة ثم resource ثم config](/ar/guide/menu-presets#الصفحة-ثم-resource-ثم-config).

## إلى 1.3

::: danger إصلاح أمني
الإصدارات من 1.0.0 إلى 1.2.1 لم تكن تطبق سياسة `view` للسجل على صفحات العلاقات ([GHSA-v3mh-w7qp-37gc](https://github.com/wit3/filament-relation-navigator/security/advisories/GHSA-v3mh-w7qp-37gc)). إذا كنت تستخدم السياسات على سجلاتك، فحدّث.
:::

- **القائمة الافتراضية الآن هي النمط `github` مع حقل البحث.** إذا كنت قد نشرت الإعدادات، فستحتفظ بما ضبطته. للإبقاء على قائمة الإصدار 1.2:

  ```php
  // config/filament-relation-navigator.php
  'menu' => [
      'preset' => 'simple',
      'searchable' => false,
  ],
  ```

- لم تعد القائمة تدرج صفحة التعديل للمستخدمين الذين لا يستطيعون تعديل السجل.
- خيارات جديدة، كلها بقيم افتراضية في الشيفرة: يستمر ملف الإعدادات المنشور من 1.2 في العمل. أضفها إلى إعداداتك عندما تريد تغييرها: `relation_manager_link` و`record_navigation` و`redirect_relation_tabs`، و`'lazy'` لـ `menu.badges`.
- إذا كنت قد نشرت قوالب الحزمة وتريد `'badges' => 'lazy'`، فانشر `menu/partials/list.blade.php` مرة أخرى.

## إلى 1.2

- يعلن `HasRelationNavigator` الآن `getRecord()` و`hasRecord()`: الصفحة التي ليس لها سجل تفشل فور تحميل صنفها، بدلًا من أن تفشل لاحقًا. صفحات العرض والتعديل والصفحات التي تستخدم `InteractsWithRecord` لديها الدالتان مسبقًا.
