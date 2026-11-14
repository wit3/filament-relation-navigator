---
layout: home

hero:
  name: Relation Navigator
  text: صفحة حقيقية لكل relation manager
  tagline: يحصل كل relation manager على صفحة ورابط خاصين به. وتنقلك قائمة مع بحث في مسار التنقل بينها. لـ Filament 4 و5.
  image:
    light: https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-light.png
    dark: https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/relation-page-dark.png
    alt: طلبات عميل في صفحتها الخاصة، مع قائمة مسار التنقل مفتوحة
  actions:
    - theme: brand
      text: ابدأ
      link: /ar/guide/quick-start
    - theme: alt
      text: عرض على GitHub
      link: https://github.com/wit3/filament-relation-navigator

features:
  - title: يكفي trait واحد، بلا أصناف صفحات
    details: أضف HasRelationPages إلى الـ resource. لا شيء لتوليده، ولا شيء تضيفه إلى getPages() أو إلى الـ panel provider.
  - title: الـ relation manager الموجود لديك
    details: الجدول والفلاتر والإجراءات والشارات والصلاحيات نفسها، على مسار خاص يمكنك الربط إليه وحفظه في المفضلة وفتحه في تبويب جديد.
  - title: قائمة في مسار التنقل
    details: كل صفحات السجل، مع الأيقونات والشارات والبحث والتنقل بلوحة المفاتيح واختصار. نمطان جاهزان أو قالب من عندك.
  - title: الترويسة والإجراءات والـ widgets
    details: اعرض بطاقة السجل والـ widgets وإجراءات الترويسة فوق العلاقة، مأخوذة من الـ resource.
  - title: السجل السابق والتالي
    details: تنقل بين السجلات في الصفحة نفسها، بترتيب قائمة السجلات التي جئت منها.
  - title: شغّل وأوقف أي شيء، في أي مكان
    details: يمكن تفعيل كل ميزة أو تعطيلها على المستوى العام، ولكل resource، ولكل صفحة أو relation manager.
---

<div class="home-section">

## من تبويب إلى صفحة

تصبح التبويبات تحت صفحة العرض صفحات مستقلة. الـ relation manager نفسه، والجدول نفسه، ورابط يمكنك مشاركته:

| قبل | بعد |
| --- | --- |
| `/customers/1?relation=orders` | `/customers/1/orders` |
| `/customers/1?relation=invoices` | `/customers/1/invoices` |

كل ما تحتاجه اثنان من الـ traits: انظر [البدء السريع](/ar/guide/quick-start).

<img class="demo" src="https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/demo.gif" alt="القائمة مفتوحة بـ Alt Shift R، مصفاة على Invoices، ثم العميل التالي في الصفحة نفسها">

</div>

<style>
.home-section { max-width: 1152px; margin: 64px auto 0; padding: 0 24px; }
.home-section h2 { border-top: 0; margin-top: 0; padding-top: 0; }
.home-section .demo { width: 100%; margin-top: 24px; border-radius: 12px; border: 1px solid var(--vp-c-divider); }
</style>
