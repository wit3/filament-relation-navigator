# وصفات

إجابات قصيرة لحالات الإعداد الشائعة. كل منها تستخدم الـ API العامة فقط.

## صفحة لبعض العلاقات فقط

اترك الـ resource كما هو وفعّل الصفحات على الـ relation managers التي تريدها:

```php
use Wit3\FilamentRelationNavigator\Concerns\InteractsWithRelationPage;

class OrdersRelationManager extends RelationManager
{
    use InteractsWithRelationPage;
}
```

## صفحات بدلًا من التبويبات

أبقِ العلاقات في صفحاتها الخاصة واحذف التبويبات من صفحة العرض. الروابط القديمة إلى التبويبات (`?relation=orders`) توصل إلى الصفحات:

```php
// ViewCustomer
public function getRelationManagers(): array
{
    return [];
}
```

```php
// config/filament-relation-navigator.php
'redirect_relation_tabs' => true,
```

تواصل القائمة و«فتح كصفحة» والبحث العام إدراج صفحات العلاقات: فهي تقرأ `getRelations()` الخاصة بالـ resource.

## تفاصيل السجل فوق كل علاقة

أعد استخدام الـ infolist الخاصة بالـ resource، أو جزء منها:

```php
// CustomerResource
public static function relationPageHeader(Schema $schema): Schema
{
    return static::infolist($schema);
}
```

## resource بلا صفحة عرض

تعمل صفحات العلاقات مع صفحة القائمة وحدها. اربطها من الجدول:

```php
Action::make('orders')
    ->url(fn (Customer $record): string => CustomerResource::getUrl('relations.orders', ['record' => $record]))
```

تحتاج إلى سياسة `view` للسجل، لأنه لا توجد صفحة عرض تقوم مقامها.

## صفحة عرض تحت مفتاح آخر

يُعثر على `'details' => ViewCustomer::route('/{record}')` من خلال صنفها. ومع صنف صفحة علاقة خاص بك، يمكنك أيضًا تسميتها: `protected static ?string $contextPage = 'details';`.

## رابط إلى صفحة علاقة من أي مكان

```php
CustomerResource::getUrl('relations.orders', ['record' => $customer]);
```

داخل tenant أو cluster أو resource متداخل، يملأ Filament المعاملات الناقصة من الطلب الحالي، كما في أي رابط لـ resource.

## قائمة هادئة

قائمة الإصدار 1.2: يفتح مسار التنقل قائمة بسيطة، بلا بحث ولا شارات ولا اختصار.

```php
'menu' => [
    'preset' => 'simple',
    'searchable' => false,
    'badges' => false,
    'shortcut' => null,
],
```

## للموظفين فقط

أخفِ صفحة علاقة لكل سجل، أو عن بعض المستخدمين، من الـ resource:

```php
public static function canAccessRelationPage(Model $record, string $slug): bool
{
    return $slug !== 'audit-logs' || auth()->user()->isStaff();
}
```

## عدة panels أو tenants

لا شيء لتفعله: تُسجَّل المسارات لكل panel فيه الـ resource، داخل الـ tenant والـ cluster الخاصين به، ويتذكر زرا السابق والتالي قائمة سجلات واحدة لكل panel وtenant وresource.
