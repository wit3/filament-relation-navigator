# حل المشكلات

## القائمة لا تظهر

- يجب أن تستخدم الصفحة `HasRelationNavigator`. صفحات العلاقات تستخدمه دائمًا.
- تحتاج القائمة إلى عنصرين على الأقل: الصفحة نفسها وعنصر آخر.
- الصفحة التي تبني عناصر مسار التنقل بنفسها يجب أن تعيد `$this->withRelationNavigator([...])`.
- تحت 40rem يخفي Filament مسار التنقل: تنتقل القائمة فوق عنوان الصفحة (`menu.mobile`).

## شكل القائمة غير صحيح أو السهم الصغير في غير مكانه

انشر ملف أنماط الإضافة مرة أخرى، بعد كل تحديث:

```bash
php artisan filament:assets
```

## صفحة العلاقة تعطي 404

- ليست للـ relation manager صفحة: يحتاج الـ resource إلى `HasRelationPages`، أو الـ relation manager إلى `InteractsWithRelationPage`، ويجب ألا يكون مستبعدًا.
- الـ slug الخاص به يطابق مفتاح صفحة في الـ resource (`edit`، `view`...): تُتخطى هذه الـ slugs. اختر غيره بـ `$relationPageSlug`.
- مع المسارات المخزنة مؤقتًا، خزنها مرة أخرى بعد النشر: `php artisan route:cache`.
- السياسة ترفض السجل على أنه «غير موجود» (`Response::denyAsNotFound()`): الـ 404 هنا مقصود.

## صفحة العلاقة تعطي 403

تحتاج صفحة العلاقة إلى `canAccess()` الخاصة بصفحة العرض، وسياسة `view` للسجل (`update` لصفحة التعديل)، و`canViewForRecord()` الخاصة بالـ relation manager، و`canAccessRelationPage()` الخاصة بالـ resource إذا عرّفتها. افحصها بهذا الترتيب.

## الشارات لا تظهر

- قيمة `menu.badges` هي `false`، أو أن الصفحة أو الـ resource يعطلها، أو الـ relation manager (`hasRelationNavigatorBadge()`).
- افتراضيًا تُحمَّل عند فتح القائمة، مع مؤشر تحميل. إذا لم يتوقف المؤشر أبدًا، فابحث في وحدة تحكم المتصفح عن طلب Livewire فاشل.
- القوالب المنشورة قبل 1.3 لا تستطيع تحميل الشارات لاحقًا: تحصل عليها مع الصفحة بدلًا من ذلك.

## الاختصار لا يفعل شيئًا

- لا يفعل شيئًا أثناء الكتابة في حقل.
- لا يُؤخذ أبدًا اختصار يستخدمه البحث العام في الـ panel (`globalSearchKeyBindings()`): اختر غيره بـ `menu.shortcut`.
- قد تستخدم إضافة في المتصفح أو نظام التشغيل المفاتيح نفسها.

## إجراء «فتح كصفحة» غير موجود

- قيمة `relation_manager_link` هي `false`، أو أن الـ resource أو الـ relation manager يعطله.
- ليست للـ relation manager صفحة خاصة، أو أنك فيها بالفعل.
- الجدول الذي يستخدم قالب `->header()` مخصصًا ليس فيه مكان لإجراءات الترويسة.

## زرا السابق والتالي غير موجودين

- هما معطلان افتراضيًا: `record_navigation`.
- يختفيان عندما لا يوجد سجل آخر (في resource متداخل، لا يوجد سجل آخر للأب نفسه).
- لاتباع ترتيب قائمة السجلات، يجب أن تكون صفحة القائمة قد فُتحت في هذه الجلسة، مع تفعيل التنقل بين السجلات في الإعدادات أو على الـ resource.

## «A directory that only this user can write»

التخزين للقراءة فقط، لذا تذهب أصناف الصفحات المولدة إلى المجلد المؤقت للنظام، وذلك المجلد يملكه مستخدم آخر. اجعل `storage/framework/cache` قابلًا للكتابة، أو احذف المجلد المذكور في الرسالة، أو اختر مجلدًا في مزود خدمة (service provider):

```php
GeneratedRelationPages::useCachePath(storage_path('app/relation-pages'));
```

## ما زلت عالقًا

اسأل في [GitHub Discussions](https://github.com/wit3/filament-relation-navigator/discussions)، أو افتح [issue](https://github.com/wit3/filament-relation-navigator/issues) مع إصدار Filament لديك وبضعة أسطر تعيد إنتاج المشكلة.
