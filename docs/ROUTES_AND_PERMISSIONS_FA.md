# مرجع Routeها و Permissionهای شیخان

این سند مسیرهای اصلی وب و نقش‌های مجاز را بر اساس routes/web.php و RolePermissionSeeder.php خلاصه می‌کند. فهرست دقیق هر action باید از فایل route و middleware همان action خوانده شود؛ route nameها قرارداد لینک‌سازی Blade هستند و URL خام نباید به‌صورت پراکنده در کد تکرار شود.

## ۱. ساختار دسترسی کلی

| دامنه | URL prefix | route name prefix | Middleware پایه |
|---|---|---|---|
| سایت عمومی | / | نام‌های عمومی | عمومی؛ مگر مسیر رسانه یا preview سیاست ویژه داشته باشد |
| حساب/خرید | / | checkout، orders، library | auth + active |
| مدیر آموزشگاه | /owner | owner. | auth + active + role:academy-owner + Permission هر عملیات |
| مدرس | /teacher | teacher. | auth + active + role:teacher + active-teacher + Permission |
| دانش‌آموز | /student | student. | auth + active + role:student + Permission |
| والد | /parent | parent. | auth + active + role:parent + Permission |
| متخصص SEO | /owner/seo | owner.seo. | auth + active + permission:seo.manage |

مسیر /dashboard یک redirect/dispatcher است؛ مقصد با نقش و دسترسی تعیین می‌شود. نقش SEO در همان پوستهٔ پنل مدیر کار می‌کند اما برای دسترسی به SEO لازم نیست نقش Academy Owner داشته باشد.

## ۲. سایت عمومی

| HTTP | الگوی URL | Route name | شرح |
|---|---|---|---|
| GET | / | home | صفحه اصلی |
| GET | /courses | courses.index | فهرست دوره‌های عمومی |
| GET | /courses/{course} | courses.show | جزئیات دوره |
| GET | /courses/{course}/lessons/{lesson}/preview | courses.lessons.preview | پیش‌نمایش درس بر اساس سیاست دسترسی |
| GET | /teachers | teachers.index | فهرست مدرس‌ها |
| GET | /teachers/{teacher} | teachers.show | پروفایل عمومی مدرس |
| GET | /store | store.index | فهرست فروشگاه |
| GET | /store/{product:slug} | store.product.show | صفحه محصول |
| GET | /blog | blog.index | فهرست وبلاگ |
| GET | /blog/{slug} | blog.show | مقاله عمومی |
| GET | /academy/{academy:slug}/content/{content:slug} | academy.content.show | محتوای عمومی وابسته به Academy |
| GET | /robots.txt | seo.robots | robots پویا |
| GET | /sitemap.xml | seo.sitemap | sitemap پویا |
| GET | /media/{media}/public | media.public | رسانه‌ای که واقعاً عمومی است |

عمومی بودن مسیر به معنی عمومی بودن هر رکورد متصل به آن نیست؛ کنترل انتشار و scope باید در query/Controller هم اجرا شود.

## ۳. ورود و حساب کاربر

| HTTP | الگوی URL | Route name | پایه دسترسی |
|---|---|---|---|
| GET | /login | login | guest |
| POST | /login | login.store | guest + throttle |
| GET | /register | register | guest |
| POST | /register | register.store | guest + throttle |
| GET | /dashboard | dashboard | auth + active |
| POST | /logout | logout | auth + active |
| GET | /media/{media}/view | media.view | auth + active + media.view |
| GET | /media/{media}/download | media.download | auth + active + media.download |

نمایش در منو، سطح دسترسی نیست. مسیر حساس باید Permission/مالکیت را در سرور بررسی کند.

## ۴. مدیر آموزشگاه

Prefix: /owner — route name prefix: owner.

| بخش | مسیرهای URL اصلی | Route nameهای کلیدی |
|---|---|---|
| داشبورد | /owner/dashboard | owner.dashboard |
| تنظیمات آموزشگاه | /owner/academy/{academy}/edit، PATCH /owner/academy/{academy} | owner.academy.edit / owner.academy.update |
| Hero و بنر | /owner/academy/{academy}/home-banners | owner.academy.banners.edit / owner.academy.banners.update |
| اعضا | /owner/academy/{academy}/people | owner.people.index |
| مدرس‌ها | /owner/academy/{academy}/people/store-teacher، /people/assign-teacher، /people/teachers/{teacher}/archive، /restore | owner.people.store-teacher / owner.people.assign-teacher / owner.people.archive-teacher / owner.people.restore-teacher |
| دانش‌آموزها | /owner/academy/{academy}/people/legacy-students، /people/enroll-student | owner.people.legacy-students.store / owner.people.enroll-student |
| کلاس‌ها/گروه‌ها | /owner/academy/{academy}/classrooms و زیردرخت create/show/edit | owner.classrooms.index / create / store / show / edit / update |
| دوره‌ها | /owner/courses و زیردرخت create/show/edit | owner.courses.index / create / store / show / edit / update |
| رسانهٔ دوره | /owner/courses/{course}/media | owner.courses.media.store / owner.courses.media.destroy |
| گزارش | /owner/reports | owner.reports.index |
| مرکز سایت | /owner/website | owner.website.index |
| Hero | /owner/website/hero | owner.website.hero.edit / owner.website.hero.update |
| محتوای Academy | /owner/content و زیردرخت create/edit | owner.content.index / create / store / edit / update |
| Showcase | /owner/academy/{academy}/showcase | owner.showcase.index |
| افتخارات | /owner/academy/{academy}/achievements و /withdraw | owner.achievements.store / owner.achievements.withdraw |
| رضایت‌مندی | /owner/academy/{academy}/testimonials و /withdraw | owner.testimonials.store / owner.testimonials.withdraw |
| منابع Academy | /owner/academy/{academy}/resources | owner.resources.index / owner.resources.store |
| مقالات | /owner/blog و زیردرخت create/edit | owner.blog.index / create / store / edit / update |
| محصولات | /owner/academy/{academy}/products | owner.products.index / owner.products.store / owner.products.publish / owner.products.archive |
| سفارش‌ها | /owner/academy/{academy}/orders | owner.orders.index |
| رسید سفارش | /owner/academy/{academy}/orders/{order}/payments/{payment}/proof | owner.orders.proof |
| تایید/رد پرداخت | همان مسیر با /confirm و /reject | owner.orders.confirm / owner.orders.reject |
| اسناد حقوقی | /owner/legal-documents | owner.legal-documents.index / store / deactivate |
| مالی | /owner/finance | owner.finance.index |

Owner فقط در scope آموزشگاه متعلق به خودش عملیات می‌کند. مشاهدهٔ مالی از مدیریت ledger مستقیم جداست. مقالهٔ Blog و محتوای Academy دو دامنهٔ جدا با مدل، فرم، URL عمومی و قواعد مالکیت متفاوت‌اند.

## ۵. مدرس

Prefix: /teacher — route name prefix: teacher.

| بخش | الگوی URL | Route nameهای کلیدی | Permission |
|---|---|---|---|
| داشبورد | /teacher/dashboard | teacher.dashboard | dashboard.view |
| پروفایل | /teacher/profile | teacher.profile.edit / teacher.profile.update | profile.view / profile.manage |
| دوره‌ها | /teacher/courses | teacher.courses.index / create / store / edit / update | courses.view / courses.manage |
| محتوای دوره | /teacher/courses/{course}/content | teacher.courses.content | lessons.view |
| پیشرفت | /teacher/courses/{course}/progress | teacher.courses.progress | courses.view |
| سرفصل دوره | /teacher/courses/{course}/sections | teacher.courses.sections.store | lessons.manage |
| درس‌ها | /teacher/lessons و /teacher/lessons/{lesson} | teacher.lessons.store / teacher.lessons.update | lessons.manage |
| رسانه | /teacher/courses/{course}/media، /teacher/lessons/{lesson}/media | media store/destroy route names | media.upload / media.manage |
| کلاس‌های گروهی | /teacher/classrooms | teacher.classrooms.index / create / store | classrooms.view / classrooms.manage |
| فهرست حضور | /teacher/attendance | teacher.attendance.index | attendance.view |
| ویرایش/ثبت حضور | /teacher/classrooms/{classroom}/attendance | teacher.classrooms.attendance.edit / store | attendance.view / attendance.manage |
| برنامه هفتگی | /teacher/schedule | teacher.schedule.index / teacher.schedule.store / teacher.schedule.destroy | live_classes.view / live_classes.manage |
| دانش‌آموزان | /teacher/students | teacher.students.index | students.view |
| تکالیف | /teacher/assignments | teacher.assignments.index / create / store / submissions | assignments.view / assignments.manage |
| نمره تکلیف | /teacher/assignments/{assignment}/submissions/{submission} | teacher.assignments.submissions.update | assignments.manage |
| آزمون | /teacher/exams | teacher.exams.index / create / store / attempts | exams.view / exams.manage |
| ارزیابی آزمون | /teacher/exam-attempts/{attempt}/grade و /grade-manual | teacher.exam-attempts.grade / grade-manual | exams.manage |
| کلاس آنلاین تاریخ‌دار | /teacher/live-classes | teacher.live-classes.index / create / store | live_classes.view / live_classes.manage |
| ضبط جلسه | /teacher/live-classes/{liveClass}/recording | teacher.live-classes.recording.store | live_classes.manage |

Teacher باید تخصیص Course/Classroom و عضویت فعال در Academy داشته باشد. وجود ID در URL یا Permission به‌تنهایی نباید اجازهٔ دسترسی به کلاس مدرس دیگر را بدهد.

## ۶. دانش‌آموز

Prefix: /student — route name prefix: student.

| بخش | الگوی URL | Route nameهای کلیدی | Permission |
|---|---|---|---|
| داشبورد | /student/dashboard | student.dashboard | dashboard.view |
| دوره‌ها | /student/courses و /student/courses/{course} | student.courses.index / student.courses.show | courses.view |
| درس | /student/lessons/{lesson} | student.lessons.show | lessons.view |
| پیشرفت درس | PATCH /student/lessons/{lesson}/progress | student.lessons.progress.update | lessons.progress |
| تکلیف | /student/assignments، /student/assignments/{assignment}، POST /submit | student.assignments.index / show / submit | assignments.view / assignments.submit |
| آزمون | /student/exams و زیردرخت show/start/attempt/result | student.exams.* | exams.view / exams.attempt |
| ارسال پاسخ | POST /student/exam-attempts/{attempt}/submit | student.exam-attempts.submit | exams.attempt |
| جلسه آنلاین تاریخ‌دار | /student/live-classes | student.live-classes.index | live_classes.view |
| ورود LiveClass | /student/live-classes/{liveClass}/join | student.live-classes.join | live_classes.view + بررسی دسترسی/زمان |
| ورود برنامه هفتگی | /student/weekly-schedule/{schedule}/join | student.schedule.join | live_classes.view + بررسی Course/Classroom/روز/زمان |
| حضور | /student/attendance | student.attendance.index | attendance.view |
| نتایج | /student/results | student.results.index | results.view |
| افتخارات | /student/achievements | student.achievements.index | achievements.view |
| یادداشت | /student/notes و زیردرخت POST/DELETE | student.notes.index / store / destroy | notes.view / notes.manage |
| پروفایل | /student/profile | student.profile.edit / student.profile.update | profile.view / profile.manage |
| امنیت حساب | PATCH /student/profile/password | student.profile.password.update | profile.security |
| منابع | /student/resources و زیردرخت view/download | student.resources.index / view / download | resources.view |

Student برای دوره پولی باید Enrollment/پرداخت معتبر داشته باشد. برای کلاس مشخص، عضویت فعال Classroom هم لازم است. باز بودن صفحهٔ لیست جلسه الزاماً به معنی فعال بودن لینک ورود نیست.

## ۷. والد

| URL | Route name | وضعیت فعلی |
|---|---|---|
| /parent/dashboard | parent.dashboard | داشبورد با role:parent و dashboard.view |

Routeهای اختصاصی والد در حال حاضر از Routeهای دانش‌آموز محدودترند. نباید با URLسازی دستی فرض کرد والد اجازه اجرای همه عملیات Student را دارد.

## ۸. خرید و کتابخانه

| HTTP/URL | Route name | کارکرد |
|---|---|---|
| GET /checkout/course/{course} | checkout.course.show | نمایش جریان خرید دوره |
| POST /checkout/course/{course} | checkout.course.store | ایجاد سفارش دوره |
| GET و POST /checkout/{product:slug} | checkout.show / checkout.store | جریان خرید محصول |
| GET /library | library.index | کتابخانه/خریدهای کاربر |
| GET /orders/{order} | orders.show | صفحه سفارش |
| POST /orders/{order}/payment-proof | orders.proof.store | ثبت رسید |
| GET /orders/{order}/files/{file}/download | orders.files.download | دانلود محافظت‌شده |

وجود Order به‌تنهایی دسترسی دوره/محصول را باز نمی‌کند. Enrollment، وضعیت پرداخت و product entitlement باید به‌صورت مستقل معتبر باشند.

## ۹. SEO

| URL | Route name | قواعد |
|---|---|---|
| /owner/seo | owner.seo.index | فهرست metadata رکوردهای مجاز |
| /owner/seo/{type}/{id}/edit | owner.seo.edit | type مجاز و ID عددی |
| PATCH /owner/seo/{type}/{id} | owner.seo.update | بررسی دوباره مجوز و مالکیت رکورد |
| /robots.txt | seo.robots | خروجی crawler |
| /sitemap.xml | seo.sitemap | URLهای عمومی قابل ایندکس |

type برای SEO محدود به academy، course، content، blog، product و teacher است. مجوز seo.manage برای تخصص SEO کافی است اما مجوز مالی یا عملیات آموزشگاه ایجاد نمی‌کند.

## ۱۰. Permissionهای کلیدی

| Permission | استفاده |
|---|---|
| dashboard.view | دیدن داشبورد نقش |
| academy.view / academy.manage | نمایش یا تغییر تنظیمات Academy |
| courses.view / courses.manage | دوره |
| classrooms.view / classrooms.manage | کلاس/گروه |
| lessons.view / lessons.manage / lessons.progress | محتوا و پیشرفت درس |
| live_classes.view / live_classes.manage | فهرست/مدیریت برنامه و جلسات آنلاین |
| attendance.view / attendance.manage | حضور و غیاب |
| assignments.view / assignments.manage / assignments.submit | تکلیف |
| exams.view / exams.manage / exams.attempt | آزمون و تلاش |
| media.view / media.upload / media.download / media.manage | فایل و رسانه |
| blog.manage | مدیریت مقالات |
| content.manage | مدیریت محتوای Academy |
| seo.manage | SEO صفحات عمومی |
| orders.view / orders.manage / finance.view | سفارش و گزارش مالی |
| resources.view / resources.manage | منابع |
| profile.view / profile.manage / profile.security | پروفایل و امنیت حساب |

فهرست کامل mapping در database/seeders/RolePermissionSeeder.php است. Seeder، authorization runtime را جایگزین نمی‌کند.

## ۱۱. اصول تغییر Route

1. در View از route name استفاده کن، نه URL دستی.
2. Route parameter را با نوع مورد انتظار محدود کن.
3. دسترسی را هم در middleware و هم در scope داده/Service بررسی کن.
4. FormRequest باید اعتبارسنجی ورودی را انجام دهد؛ select یا hidden field مجوز نیست.
5. Route ورود به جلسه باید در backend Course، Classroom، زمان و URL را بررسی کند.
6. مسیر رسانهٔ private را به endpoint عمومی media.public وصل نکن.
7. اگر action در routes/web.php نیست، از نام مشابه نتیجه نگیر که پیاده‌سازی شده؛ route، authorization و test باید با هم اضافه شوند.
