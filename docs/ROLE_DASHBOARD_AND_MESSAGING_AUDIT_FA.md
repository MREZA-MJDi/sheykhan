# ممیزی مسیرهای داشبورد شیخان و پیام‌رسانی دوطرفه

## وضعیت بررسی
- پروژه Laravel 12 با Blade/Vite است.
- شاخه کار: `fix/message-flow-audit` (جدا از `main`).
- PR پیش‌نویس: https://github.com/MREZA-MJDi/sheykhan/pull/69
- پیام دانش‌آموز به مدرس در `StudentMessage` ذخیره می‌شود؛ پاسخ مدرس در همان رکورد و همان فهرست دانش‌آموز قابل نمایش است.
- پیام مستقیم مدرس به دانش‌آموز/ولی جریان جداگانه‌ای با مدل `TeacherDirectMessage` دارد.
- پیام‌های عمومی/کلاسی `AcademyMessage` نیز جریان سومی هستند؛ این‌ها اعلان و پیام یک‌به‌چندند و نباید با گفت‌وگوی خصوصی قاطی شوند.
- در دو مدل مکاتبه، فیلد واحد `response` نشان می‌دهد که مسیر فعلی «پیام + یک پاسخ» است، نه گفت‌وگوی چندپیامی با تاریخچهٔ نامحدود. برای مکالمه واقعی باید Conversation/Message جدا با چند ردیف، ترتیب زمانی، وضعیت خواندن، و دسترسی مبتنی بر participant طراحی شود.

## قواعد دسترسی و آزمون پذیرش
1. دانش‌آموز تنها معلم فعالی را انتخاب کند که از طریق enrollment فعال و assignment معتبر دوره/کلاس با او ارتباط آموزشی دارد.
2. هنگام POST، شناسه‌ی ارسالی مجدداً در سرور اعتبارسنجی شود؛ مخفی بودن option در UI کافی نیست.
3. معلم فقط مکاتباتی را ببیند که به خودش، عضویت فعال Academy و assignment فعال دوره/کلاس مربوط است.
4. تغییر assignment یا عضویت به غیرفعال، باید مطابق سیاست روشن‌شدهٔ سامانه بر دسترسی پیام‌های خصوصی اثر بگذارد؛ این رفتار باید با تست مشخص شود.
5. مخاطب پیام مستقیم، گیرنده و فرستنده باید با رکورد احراز هویت‌شده تطبیق داده شوند؛ حدس زدن ID نباید پیام را افشا کند.
6. پاسخ‌ها باید برای هر دو طرف در کارتابل همان ارتباط نمایش داده شوند و هیچ پاسخ دانش‌آموز در بخش پیام‌های ارسال‌شدهٔ معلم گم نشود.
7. دانش‌آموزان نباید فهرست یا پیام خصوصی یکدیگر را ببینند؛ معلم نیز نباید پیام دانش‌آموزی را ببیند که متعلق به کلاس/دورهٔ فعال او نیست.
8. شاخه‌های پیام‌رسانی فعلی در رابط کاربری جدا هستند: پیام دانش‌آموز به معلم در `StudentMessage`، پیام مستقیم معلم به دانش‌آموز/ولی در `TeacherDirectMessage` و اعلان‌ها در `AcademyMessage`. محصول نهایی باید این تفاوت نوع پیام را برای کاربر قابل فهم کند و ارسال/دریافت و پاسخ هر مسیر را end-to-end تست کند.
9. صفحات teacher/student باید layout، جهت RTL، رنگ‌ها، اندازه‌ی کارت‌ها، فاصله‌گذاری، فهرست‌های خالی، خطاهای فرم و موفقیت عملیات سازگار داشته باشند.

## مسیرهای بررسی‌شده
- `app/Http/Controllers/Student/StudentMessageController.php`
- `app/Http/Controllers/Teacher/StudentMessageController.php`
- `app/Http/Controllers/Teacher/DirectMessageController.php`
- `app/Http/Controllers/Teacher/AcademyMessageController.php`
- `resources/views/student/messages/index.blade.php`
- `resources/views/teacher/student-messages/index.blade.php`
- `resources/views/teacher/direct-messages/index.blade.php`
- `resources/views/components/communication/teacher-direct-messages-inbox.blade.php`
- `resources/views/layouts/student.blade.php`
- `resources/views/layouts/teacher.blade.php`
- `resources/views/student/dashboard.blade.php`
- `resources/views/teacher/dashboard.blade.php`
- `routes/web.php`
- `tests/Feature/StudentTeacherMessagingTest.php`
- `tests/Feature/StudentTeacherDashboardContractTest.php`
- `tests/Feature/StudentDashboardReferenceDesignTest.php`

## نقشه اجرای مرحله‌ای
### فاز ۱ — ایمنی و مسیرهای پیام
- route:list را برای تمام routeهای student/teacher با middleware واقعی استخراج و با نام‌های استفاده‌شده در Blade تطبیق بده.
- تست‌های Feature برای رد دسترسی بین دو دانش‌آموز، بین دو معلم، دستکاری شناسه‌ها و لغو enrollment/assignment اجرا یا تکمیل شوند.
- ارسال، دریافت پاسخ، وضعیت خوانده‌شدن و اعلان‌ها از دید هر دو نقش end-to-end تست شوند.
- تصمیم محصولی: «مکاتبه با یک پاسخ» حفظ شود یا تبدیل به conversation چندپیامی شود. درخواست فعلی دوطرفه به‌صورت thread واقعی بهتر پوشش داده می‌شود.

### فاز ۲ — Design System مرجع
- تصویر مرجع به‌عنوان baseline: پوستهٔ سایدبار، topbar، پس‌زمینه روشن، کارت‌های سفید، بنفش/آبی، نمودار و جداول با ابعاد و فاصله ثابت.
- راست‌چین سراسری و یکسان‌سازی typography، border-radius، spacing، shadow، hover/focus و responsive breakpoints.
- از بازسازی داشبورد دانش‌آموز از صفر خودداری شود؛ `student-dashboard-reference.css` و قراردادهای تست موجود را حفظ و به صفحات داخلی تعمیم بده.
- داشبورد معلم باید با همان scale و rhythm هماهنگ شود؛ داده‌های مالی نامربوط وارد KPIهای معلم نشوند.
- صفحات داخلی: کلاس‌ها، تکالیف، آزمون‌ها، حضور و غیاب، برنامه، نمرات/نتایج، منابع، پروفایل و پیام‌ها با همان card/table/form primitives بازبینی شوند.

### فاز ۳ — QA و تحویل
- تست‌ها با `php artisan test` و ساخت asset با `npm run build` اجرا شوند.
- برای داشبورد و صفحات داخلی هر نقش اسکرین‌شات در عرض دسکتاپ و موبایل گرفته شود.
- هر تعامل اصلی با مسیر برگشت بررسی شود: submit، validation error، success/flash، pagination، empty state، back navigation و logout.
- تغییرات در PR و بدون merge خودکار ارائه شوند تا diff، تست و screenshotها بازبینی شوند.

## محدودیت این commit
این فایل گزارش ممیزی اولیه و برنامه اجرایی است؛ تست یا تغییر رفتاری انجام‌شده را ادعا نمی‌کند. تغییرات عملیاتی باید با اجرای واقعی تست‌ها و بررسی Viewها تحویل شوند.
