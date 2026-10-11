# کاتالوگ قابلیت‌های شیخان

این سند نقشهٔ عملیاتی قابلیت‌های موجود در ریپوی شیخان است. هدف این نیست که قابلیت احتمالی یا ایدهٔ آینده را به‌عنوان محصول آماده معرفی کنیم؛ هرجا اتصال خارجی یا مسیر مدیریتی کامل نیست، صریحاً مشخص شده است.

## ۱. از کجا شروع کنم؟

| من چه کسی هستم؟ | مسیر شروع | کاربرد اصلی |
|---|---|---|
| بازدیدکننده سایت | / | دیدن محتوای عمومی، دوره‌ها، مدرس‌ها، فروشگاه و مقالات |
| دانش‌آموز | /dashboard → /student/dashboard | ادامه آموزش، دوره‌های ثبت‌نام‌شده، تکلیف، آزمون، کلاس آنلاین و نتیجه |
| مدرس | /dashboard → /teacher/dashboard | دوره‌های واگذارشده، کلاس‌ها، برنامه هفتگی، جلسه آنلاین و بررسی تکلیف/آزمون |
| مدیر آموزشگاه | /dashboard → /owner/dashboard | مدیریت آموزشگاه، اعضا، دوره‌ها، گروه‌ها، سایت، محتوای عمومی، سفارش و گزارش |
| متخصص SEO | /dashboard → /owner/seo | بهینه‌سازی صفحات عمومی در همان پوسته پنل مدیر؛ بدون دسترسی عملیاتی آموزشگاه |
| والد | /dashboard → /parent/dashboard | نمای کلی مرتبط با فرزندان متصل‌شده به حساب والد |

مسیر /dashboard یک ورودی هدایت‌شونده است؛ مقصد واقعی بر اساس نقش و دسترسی حساب تعیین می‌شود. باز بودن یک لینک در منو به‌تنهایی مجوز دسترسی نیست؛ Route middleware، Permission و scope داده در backend تصمیم نهایی را می‌گیرند.

## ۲. واژه‌نامهٔ دامنه

### آموزشگاه (Academy)
مرز مالکیت داده و فضای آموزشی است. مدیر آموزشگاه می‌تواند فقط دادهٔ آموزشگاه‌های متعلق به خودش را مدیریت کند. عضویت فعال مدرس و دانش‌آموز نیز برای دسترسی به داده‌های آموزشی اهمیت دارد.

### دوره (Course)
محتوای آموزشی سطح بالاتر است؛ دوره شامل سرفصل، درس‌ها و ارتباط با مدرس‌هاست. ثبت‌نام و پرداخت دوره از عضویت در یک گروه خاص جداست.

### کلاس یا گروه (Classroom)
گروهی از دانش‌آموزان مرتبط با یک دوره، با مدرس‌های تخصیص‌یافته و برنامهٔ هفتگی مشخص. کلاس/گروه را با جلسه آنلاین تاریخ‌دار اشتباه نگیرید.

### درس (Lesson)
واحد محتوایی داخل سرفصل دوره است. نمایش درس و ثبت پیشرفت آن تابع انتشار درس و مجوز دسترسی به دوره است.

### برنامهٔ هفتگی (ClassSchedule)
قانون تکرارشونده برای یک Classroom: روز هفته، ساعت شروع/پایان، عنوان اتاق، سرویس تماس و لینک اتاقی که هر هفته بازاستفاده می‌شود. این رکورد یک نوبت تاریخ‌دار مستقل نیست.

### جلسهٔ آنلاین تاریخ‌دار (LiveClass)
یک رویداد دارای تاریخ/ساعت، مدت، سرویس تماس و لینک مشخص. به یک دوره و در صورت نیاز یک Classroom متصل می‌شود. ضبط جلسه نیز به همین رویداد متصل می‌شود.

### ثبت‌نام (CourseEnrollment)
رابطهٔ دانش‌آموز با دوره است. برای دوره پولی، داشتن سفارش به‌تنهایی کافی نیست؛ وضعیت پرداخت و Enrollment معتبر برای بازشدن محتوای محافظت‌شده لازم است.

## ۳. سایت عمومی

### صفحهٔ اصلی
مسیر: /  
داده‌های عمومی از سرویس‌ها و رکوردهای سایت خوانده می‌شوند. کارت جلسهٔ آنلاین در صفحه اصلی نقش معرفی جلسه را دارد و در صورت داشتن دوره، به صفحهٔ دوره می‌رود؛ کارت عمومی جای مسیر امن ورود دانش‌آموز به جلسه نیست.

### فهرست دوره‌ها و جزئیات
مسیرها:
- /courses
- /courses/{course}
- /courses/{course}/lessons/{lesson}/preview

فهرست، دوره‌های قابل انتشار را نمایش می‌دهد. صفحهٔ دوره توضیحات و اطلاعات مرتبط را نشان می‌دهد. مسیر preview مخصوص محتوایی است که سیاست پیش‌نمایش اجازه داده باشد؛ نباید آن را راه دورزدن ثبت‌نام یا پرداخت فرض کرد.

### فهرست مدرس‌ها و پروفایل عمومی
مسیرها:
- /teachers
- /teachers/{teacher}

نمایش عمومی به وضعیت حساب، نقش مدرس و تنظیمات عمومی/تأییدشدهٔ پروفایل وابسته است. وجود حساب مدرس در سیستم الزاماً به معنی عمومی بودن صفحهٔ او نیست.

### فروشگاه محصولات
مسیرها:
- /store
- /store/{product:slug}

محصول فروشگاه با دورهٔ آموزشی یکی نیست. مسیر خرید محصول، سفارش و entitlement محصول با Enrollment دوره قواعد جداگانه دارند.

### وبلاگ
مسیرها:
- /blog
- /blog/{slug}

مقاله عمومی باید منتشر شده و زمان انتشار آن رسیده باشد. مدیریت مقاله در پنل، از مدیریت محتوای وابسته به Academy جداست.

### محتوای عمومی آموزشگاه
مسیر:
- /academy/{academy:slug}/content/{content:slug}

این دامنه برای محتوای مقاله‌ای یا ویدئویی وابسته به Academy است. محتوای ویدئویی فقط زمانی عمومی نمایش داده می‌شود که فایل واقعی و وضعیت رسانه با سیاست انتشار سازگار باشد. پیش‌نویس یا ویدئوی بدون فایل معتبر نباید مثل صفحه عمومی منتشرشده رفتار کند.

### Robots و Sitemap
مسیرها:
- /robots.txt
- /sitemap.xml

این مسیرها خروجی واقعی crawler-facing هستند. صفحه‌های noindex از sitemap کنار گذاشته می‌شوند. وجود URL در sitemap تضمین رتبهٔ گوگل نیست؛ ایندکس واقعی باید بعد از استقرار دامنه در ابزارهای وبمستر بررسی شود.

## ۴. پنل مدیر آموزشگاه

Route prefix: /owner  
گروه عمومی پنل با role مربوط به Academy Owner محافظت می‌شود و مجوز هر عملیات نیز جداگانه بررسی می‌شود.

### داشبورد
Route name: owner.dashboard  
نمای کلان عملیات آموزشگاه و دسترسی سریع به بخش‌های مجاز. این صفحه جای کار اجرایی مدرس مثل نمره‌دهی یا ثبت حضورغیاب نیست.

### تنظیمات آموزشگاه و سایت
مسیرهای مهم:
- owner.academy.edit / owner.academy.update
- owner.website.index
- owner.website.hero.edit / owner.website.hero.update

تنظیمات آموزشگاه، وضعیت سایت و Hero/Banner را مدیریت می‌کنند. تغییر تصویر Hero با مدیریت رسانهٔ دوره یا تصویر OG مربوط به SEO یکی نیست؛ کاربرد و نسبت تصویر هر مورد متفاوت است.

### اعضا و ثبت‌نام
مسیرهای مهم:
- owner.people.index
- owner.people.store-teacher
- owner.people.legacy-students.store
- owner.people.assign-teacher
- owner.people.enroll-student
- owner.people.archive-teacher / owner.people.restore-teacher

مدیر می‌تواند در scope آموزشگاه، مدرس را اضافه کند یا تخصیص دهد و دانش‌آموز را وارد جریان ثبت‌نام کند. آرشیو مدرس باید تاریخچه را حفظ کند؛ حذف/آرشیو دسترسی و تاریخچه مفاهیم جداگانه‌اند.

### مدیریت کلاس/گروه
مسیرهای مهم:
- owner.classrooms.index
- owner.classrooms.create
- owner.classrooms.store
- owner.classrooms.show
- owner.classrooms.edit
- owner.classrooms.update

کلاس گروهی است که به Academy و Course وابسته است و مدرس/دانش‌آموزان آن با عضویت‌های مجاز تعیین می‌شوند. این بخش تنظیم گروه است، نه صفحهٔ ایجاد تک جلسهٔ آنلاین مدرس.

### مدیریت دوره و رسانه
مسیرهای مهم:
- owner.courses.index
- owner.courses.create
- owner.courses.store
- owner.courses.show
- owner.courses.edit
- owner.courses.update
- owner.courses.media.store / owner.courses.media.destroy

مدیر می‌تواند دوره را در scope آموزشگاه مدیریت کند و رسانهٔ مربوط را بارگذاری/مدیریت کند. محتوای دوره و صفحهٔ عمومی دوره باید با وضعیت انتشار و مجوز دسترسی هماهنگ بمانند.

### گزارش‌ها و مالی
مسیرهای مهم:
- owner.reports.index
- owner.finance.index
- owner.orders.index
- owner.orders.proof
- owner.orders.confirm
- owner.orders.reject

گزارش مالی با تراکنش‌های معتبر کار می‌کند. تأیید/رد پرداخت عملیات مشخص سفارش است؛ دستکاری مستقیم ledger مالی نباید جایگزین گردش کار سفارش/تراکنش شود.

### محتوا، افتخارات، رضایت‌مندی و منابع
مسیرهای مهم:
- owner.content.index / owner.content.create / owner.content.store / owner.content.edit / owner.content.update
- owner.showcase.index
- owner.achievements.store / owner.achievements.withdraw
- owner.testimonials.store / owner.testimonials.withdraw
- owner.resources.index / owner.resources.store

محتوای Academy برای انتشار عمومی یا نمایش در صفحات سایت است. افتخارآفرین و رضایت‌مندی رکوردهای جداگانه‌اند و منبع آموزشی نیز فایل/رکورد مرتبط با آموزشگاه است. وضعیت انتشار، رسانهٔ پیوست و scope آموزشگاه باید هماهنگ باشند.

### مقالات
مسیرهای مهم:
- owner.blog.index
- owner.blog.create / owner.blog.store
- owner.blog.edit / owner.blog.update

فهرست مقاله‌های مالک، با مقاله‌های عمومی کل سایت یکی نیست. در جریان فعلی، سرویس مدیریت مقاله به نویسندهٔ همان رکورد scope می‌دهد. عنوان، slug، خلاصه، متن، دسته، وضعیت انتشار، زمان انتشار و تصویر شاخص مدیریت می‌شوند.

### SEO
مسیرهای مهم:
- owner.seo.index
- owner.seo.edit
- owner.seo.update

SEO به metadata صفحات واقعی عمومی مربوط است: title، description، canonical، robots، OpenGraph و Schema JSON. امتیاز تکمیل فیلدها فقط کیفیت پرشدن metadata را بیان می‌کند؛ رتبه یا traffic واقعی گوگل نیست.

متخصص SEO در همان پوستهٔ پنل مدیر آموزشگاه کار می‌کند اما فقط مجوز seo.manage را دارد. این نقش نباید به‌خاطر دسترسی SEO مجوز مالی، دانش‌آموز، آموزشگاه یا سفارش دریافت کند.

### اسناد حقوقی
مسیرهای مهم:
- owner.legal-documents.index
- owner.legal-documents.store
- owner.legal-documents.deactivate

مدیریت اسناد حقوقی از انتشار محتوای عمومی و مدیریت SEO جداست. غیرفعال‌سازی سند با پاک کردن تاریخچهٔ رضایت/سوابق مفهوم یکسانی ندارد.

## ۵. پنل مدرس

Route prefix: /teacher  
گروه مدرس علاوه بر نقش teacher به وضعیت مدرس فعال و Permissionهای عملیاتی وابسته است. دامنهٔ اصلی، دوره‌ها و Classroomهای تخصیص‌یافته در Academy فعال است.

### داشبورد مدرس
Route: /teacher/dashboard  
نمای روز، جلسات و برنامه، کلاس‌ها، تعداد دانش‌آموزان، کارهای ارزیابی در انتظار بررسی و نمودارهای آموزشی. KPI مالی به‌عنوان وظیفهٔ مدرس نمایش داده نمی‌شود.

### دوره‌های من
مسیرها:
- /teacher/courses
- /teacher/courses/create
- /teacher/courses/{course}/edit
- /teacher/courses/{course}/content
- /teacher/courses/{course}/progress
- /teacher/courses/{course}/sections
- /teacher/lessons

این بخش برای Courseهای واگذارشده به مدرس است. ساختار دوره به سرفصل‌ها و درس‌ها تقسیم می‌شود؛ ثبت درصد پیشرفت دانش‌آموز در Student workspace انجام می‌شود و مدرس آن را مشاهده/پیگیری می‌کند.

### کلاس‌های من
مسیرها:
- /teacher/classrooms
- /teacher/classrooms/create
- /teacher/classrooms

Classroom گروه دانش‌آموزان است. انتخاب Classroom هنگام ساخت LiveClass یعنی جلسه فقط برای دانش‌آموزان واجد شرایط همان گروه نمایش داده شود؛ خالی گذاشتن Classroom در فرم LiveClass یعنی مخاطب جلسه، دانش‌آموزان واجد شرایط دوره است.

### برنامه هفتگی
مسیرها:
- /teacher/schedule
- POST /teacher/schedule
- DELETE /teacher/schedule/{schedule}

هر ردیف یک بازهٔ تکرارشونده در هفته است. هم‌پوشانی بازه‌های یک Classroom در همان روز رد می‌شود. provider و meeting_url روی همین ردیف ذخیره می‌شوند و یک لینک ثابت برای تکرار همان بازه در هر هفته دارند.

### کلاس‌های آنلاین
مسیرها:
- /teacher/live-classes
- /teacher/live-classes/create
- POST /teacher/live-classes
- POST /teacher/live-classes/{liveClass}/recording

هر رکورد LiveClass یک جلسهٔ تاریخ‌دار و مدت‌دار است؛ ممکن است به Course و یک Classroom مشخص متصل شود. پایان زمان محاسبه‌شده از scheduled_at و duration_minutes در scheduled_end_at ذخیره می‌شود.

در فرم provider:
- Jitsi: لینک اتاق یکتا فوراً در فرم ساخته می‌شود و اگر خالی ارسال شود backend نیز لینک می‌سازد.
- Google Meet: لینک واقعی باید در Google Meet ساخته و در فرم قرار داده شود.
- Zoom: لینک واقعی باید در Zoom ساخته و در فرم قرار داده شود.
- سایر: لینک HTTPS واقعی سرویس باید وارد شود.

شیخان در وضعیت فعلی اتصال OAuth/API برای ساخت جلسه از طرف Google Meet یا Zoom ندارد؛ ساختن یک URL حدسی و نشان دادن آن به‌عنوان جلسهٔ معتبر کار درستی نیست.

### ضبط جلسه
روی کارت LiveClass، مدرس می‌تواند فایل ضبط‌شده را بارگذاری و زمان انتشار را تعیین کند. فایل به‌صورت private ذخیره می‌شود. دسترسی دانش‌آموز تابع دوره، Classroom، زمان انتشار ضبط و permission رسانه است؛ دانستن URL فایل به‌تنهایی نباید دسترسی ایجاد کند.

### حضور و غیاب
مسیرها:
- /teacher/attendance
- /teacher/classrooms/{classroom}/attendance
- POST /teacher/classrooms/{classroom}/attendance

تاریخ می‌تواند از UI شمسی دریافت و در backend به تاریخ موردنیاز ذخیره شود. ثبت برای دانش‌آموزان دارای عضویت فعال همان Classroom است. یکتایی رکورد ترکیب Classroom، دانش‌آموز و تاریخ مانع ایجاد ثبت تکراری می‌شود.

### تکلیف و آزمون
مسیرهای مهم:
- /teacher/assignments
- /teacher/assignments/create
- /teacher/assignments/{assignment}/submissions
- /teacher/assignments/{assignment}/submissions/{submission}
- /teacher/exams
- /teacher/exams/create
- /teacher/exams/{exam}/attempts
- /teacher/exam-attempts/{attempt}/grade
- /teacher/exam-attempts/{attempt}/grade-manual

تکلیف ارسالی و تلاش آزمون از رکورد پایهٔ تکلیف/آزمون جدا هستند. مدرس فقط موردهای حوزهٔ دوره‌های واگذارشده و وضعیت مجاز را بررسی می‌کند.

### دانش‌آموزان، منابع و پروفایل
مسیرهای مهم:
- /teacher/students
- /teacher/profile
- /teacher/courses/{course}/media
- /teacher/lessons/{lesson}/media

فهرست دانش‌آموزان و منابع در scope مجاز مدرس محدود می‌شود. تغییر پروفایل و بارگذاری رسانه باید از مسیر permission مربوط استفاده کند.

## ۶. پنل دانش‌آموز

Route prefix: /student

### داشبورد
Route: /student/dashboard  
اولویت آن ادامه آموزش، قدم بعدی، جلسه‌ها، تکالیف، آزمون، پیشرفت، منابع و نتایج است. داده‌های نمایش‌داده‌شده از دسترسی واقعی دانش‌آموز و رکوردهای آموزشی مشتق می‌شوند.

### دوره‌های من
مسیرها:
- /student/courses
- /student/courses/{course}
- /student/lessons/{lesson}
- PATCH /student/lessons/{lesson}/progress

این بخش CourseEnrollmentهای معتبر را نمایش می‌دهد. برای دورهٔ پولی، پرداخت کامل و Enrollment معتبر لازم است؛ تکمیل یک lesson و کل پیشرفت Course داده‌های مرتبط اما متمایز هستند.

### کلاس‌های آنلاین
مسیرها:
- /student/live-classes
- /student/live-classes/{liveClass}/join
- /student/weekly-schedule/{schedule}/join

صفحهٔ کلاس‌های آنلاین دو نوع برنامه را جدا می‌کند:
1. جلسهٔ تاریخ‌دار که با LiveClass ساخته شده است.
2. برنامهٔ هفتگی ثابت که برای Classroom تعریف شده است.

دسترسی به لینک از backend بررسی می‌شود. برای LiveClass زمان مجاز از ۱۵ دقیقه قبل شروع تا پایان زمان برنامه‌ریزی‌شده است؛ برای برنامهٔ هفتگی نیز روز و ساعت تکرارشونده، عضویت فعال در کلاس، Course قابل‌دسترسی و لینک معتبر باید همگی برقرار باشند.

### تکلیف، آزمون و نتایج
مسیرهای مهم:
- /student/assignments
- /student/assignments/{assignment}
- POST /student/assignments/{assignment}/submit
- /student/exams
- /student/exams/{exam}
- POST /student/exams/{exam}/start
- /student/exams/{exam}/attempts/{attempt}
- POST /student/exam-attempts/{attempt}/submit
- /student/exams/{exam}/result
- /student/results

ثبت پاسخ یا ارسال تکلیف باید تحت دسترسی همان رکورد آموزشی باشد. تلاش آزمون، پاسخ‌های ارسال‌شده و نتیجهٔ ارزیابی تاریخچه‌های جدا هستند.

### حضورغیاب، یادداشت و افتخارات
مسیرها:
- /student/attendance
- /student/notes
- POST /student/lessons/{lesson}/notes
- DELETE /student/notes/{note}
- /student/achievements

یادداشت شخصی دانش‌آموز متعلق به حساب اوست. حضورغیاب و افتخارات از رکوردهای آموزشی معتبر خوانده می‌شوند و نباید با دادهٔ متنی ثابت یا نمونه اشتباه گرفته شوند.

### منابع و پروفایل
مسیرها:
- /student/resources
- /student/resources/{resource}/view
- /student/resources/{resource}/download
- /student/profile
- PATCH /student/profile
- PATCH /student/profile/password

دسترسی منابع باید بر اساس مجوز و scope دانش‌آموز باشد. نمایش داخل سایت و دانلود فایل عملیات متفاوت‌اند؛ در مورد ضبط جلسات، دانلود ممکن است عمداً بسته باشد و دسترسی فقط از viewer مجاز انجام شود.

## ۷. پنل والد

Route فعلی اصلی: /parent/dashboard

پنل والد در حال حاضر بر dashboard تجمیعی تکیه دارد. اطلاعات فرزند فقط وقتی قابل نمایش است که رابطهٔ والد/دانش‌آموز و scope دسترسی معتبر باشد. از routeهای فعلی نباید نتیجه گرفت که والد تمام endpointهای دانش‌آموز را مستقیماً در اختیار دارد؛ مسیرهای اختصاصی والد هنوز محدودترند.

## ۸. خرید، سفارش و دسترسی

مسیرهای مهم:
- /checkout/course/{course}
- /checkout/{product:slug}
- /orders/{order}
- POST /orders/{order}/payment-proof
- /orders/{order}/files/{file}/download
- /library

جریان استاندارد:
1. کاربر دوره یا محصول را انتخاب می‌کند.
2. سفارش و آیتم سفارش ساخته می‌شوند.
3. پرداخت/رسید در مسیر مربوط ثبت و بررسی می‌شود.
4. دسترسی دوره از Enrollment/وضعیت پرداخت و دسترسی محصول از entitlement محصول کنترل می‌شود.

وجود Order مساوی دسترسی نهایی نیست. برای محصول خریداری‌شده، entitlement و سیاست دانلود فایل نیز جداگانه بررسی می‌شود.

## ۹. رسانه و فایل

دامنهٔ رسانه از Media و اتصال‌های آن استفاده می‌کند. سیاست نمایش به visibility، status، collection و مسیر استفاده وابسته است:
- رسانهٔ عمومی سایت،
- فایل/ضبط خصوصی دانش‌آموز،
- تصویر شاخص محتوا،
- رسانهٔ دوره و درس،
- فایل محصول و دانلود محافظت‌شده.

لینک عمومی یا hash فایل به‌تنهایی کنترل دسترسی نیست. هر فایل خصوصی باید از endpoint دارای authorization عبور کند.

## ۱۰. مرزهای امنیتی مشترک

- Owner باید مالک Academy مربوط باشد.
- Teacher باید تخصیص Course/Classroom و عضویت فعال Academy داشته باشد.
- Student باید CourseEnrollment معتبر و عضویت فعال لازم را داشته باشد.
- دسترسی دورهٔ پولی باید پرداخت کامل را بررسی کند.
- LiveClass و برنامهٔ هفتگی، هم scope آموزشی و هم پنجرهٔ زمانی ورود دارند.
- نقش SEO فقط permission سئوی صفحات عمومی دارد.
- media.view و media.download مجوزهای متفاوت‌اند.
- route را فقط با مخفی کردن لینک در منو امن نکنید؛ authorization حتماً باید server-side بماند.

## ۱۱. قابلیت‌های جزئی یا محدودیت‌های فعلی

این موارد نباید به‌عنوان یکپارچگی کامل تبلیغ شوند:

- Google Meet و Zoom: ثبت لینک واقعی پشتیبانی می‌شود؛ ایجاد خودکار اتاق از طرف این providerها بدون OAuth/API و تنظیم حساب provider انجام نمی‌شود.
- وضعیت LiveClass دارای مقدارهایی مانند scheduled، live، ended، cancelled و completed است، اما مسیرهای فعلی ساخت/فهرست/آپلود ضبط را عرضه می‌کنند؛ نباید بدون وجود route و منطق مربوط فرض کرد همهٔ تغییرات lifecycle از UI در دسترس‌اند.
- داشبوردها خلاصهٔ کار روزمره‌اند؛ جایگزین صفحهٔ تخصصی برای مدیریت رکوردها نیستند.
- امتیاز تکمیل SEO معیار پرشدن فیلدهاست، نه بازدید، نرخ کلیک یا رتبهٔ واقعی Google.
- حضور route یا فایل View به‌تنهایی اثبات نمی‌کند که یک قابلیت از ابتدا تا انتها کامل است؛ تست Feature و بررسی دادهٔ واقعی باید آن را پشتیبانی کند.

## ۱۲. مسیرهای مرجع کد

- Route registry: ../routes/web.php
- سطح دسترسی و نقش‌ها: ../database/seeders/RolePermissionSeeder.php
- دسترسی دانش‌آموز: ../app/Services/StudentAccessService.php
- فهرست و دسترسی Course دانش‌آموز: ../app/Services/StudentCourseService.php
- فضای کاری مدرس: ../app/Services/TeacherWorkspaceService.php
- داشبورد مدرس: ../app/Services/TeacherDashboardService.php
- داشبورد دانش‌آموز: ../app/Services/StudentDashboardService.php
- مدیریت مقاله: ../app/Services/OwnerBlogService.php
- مدیریت محتوای آموزشگاه: ../app/Services/OwnerContentService.php
- مدیریت SEO: ../app/Services/OwnerSeoService.php
- سفارش و پرداخت: ../app/Http/Controllers/Commerce
- تست‌های Feature: ../tests/Feature

## ۱۳. تست حداقلی قبل از انتشار

~~~bash
php artisan optimize:clear
php artisan migrate
php artisan test
npm run build
~~~

برای تغییرهای مربوط به جلسه آنلاین، علاوه بر suite کامل، تست‌های TeacherWorkspaceFlowTest و StudentSessionAccessTest را اجرا کنید. روی محیط آزمایشی، موارد Jitsi، Google Meet با URL واقعی، لینک provider ناسازگار، عضویت دانش‌آموز، پنجره مجاز ورود و انتشار ضبط را دستی نیز بررسی کنید.
