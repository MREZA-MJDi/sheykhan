# ممیزی مسیرهای داشبورد شیخان و پیام‌رسانی دوطرفه

## وضعیت بررسی
- پروژه Laravel 12 با Blade/Vite است.
- شاخه بررسی: `fix/message-flow-audit` (جدا از `main`).
- پیام دانش‌آموز به مدرس در `StudentMessage` ذخیره می‌شود؛ پاسخ مدرس در همان رکورد و همان فهرست دانش‌آموز قابل نمایش است.
- پیام مستقیم مدرس به دانش‌آموز/ولی جریان جداگانه‌ای با مدل `TeacherDirectMessage` دارد.
- پیام‌های عمومی/کلاسی `AcademyMessage` نیز جریان سومی هستند؛ باید از پیام خصوصی تفکیک بمانند.

## قواعد دسترسی که باید در آزمون‌ها پوشش داده شوند
1. دانش‌آموز تنها معلم فعال دوره‌ای را انتخاب کند که enrollment فعال دارد، یا معلم فعالی که به کلاس فعال خودش تخصیص داده شده.
2. هنگام POST، شناسه‌ی ارسالی مجدداً در سرور اعتبارسنجی شود؛ مخفی بودن option در UI کافی نیست.
3. معلم فقط مکاتباتی را ببیند که به خودش، عضویت فعال Academy و assignment فعال دوره/کلاس مربوط است.
4. تغییر assignment یا عضویت به غیرفعال، دسترسی تاریخی به پیام خصوصی را نیز ببندد.
5. مخاطب پیام مستقیم، گیرنده و فرستنده باید با رکورد احراز هویت‌شده تطبیق داده شوند؛ حدس زدن ID نباید پیام را افشا کند.
6. پاسخ‌ها باید برای هر دو طرف در کارتابل مربوط به همان گفت‌وگو نشان داده شوند.
7. پیام‌های اعلان کلاس/آموزشگاه با گفت‌وگوی خصوصی دانش‌آموز-معلم یکی نشوند.
8. صفحات teacher/student باید layout، جهت RTL، رنگ‌ها، اندازه‌ی کارت‌ها، فاصله‌گذاری و وضعیت‌های loading/empty/error مشترک و سازگار داشته باشند.

## مسیرهای بررسی‌شده
- `app/Http/Controllers/Student/StudentMessageController.php`
- `app/Http/Controllers/Teacher/StudentMessageController.php`
- `app/Http/Controllers/Teacher/DirectMessageController.php`
- `app/Http/Controllers/Teacher/AcademyMessageController.php`
- `resources/views/student/messages/index.blade.php`
- `resources/views/teacher/student-messages/index.blade.php`
- `resources/views/teacher/direct-messages/index.blade.php`
- `resources/views/components/communication/teacher-direct-messages-inbox.blade.php`
- `routes/web.php`

## مواردی که نیاز به ادامه ممیزی دارند
- تمام routeها و middlewareهای teacher/student در `routes/web.php` با route:list واقعی تطبیق داده شوند.
- تست‌های Feature برای رد دسترسی بین دو دانش‌آموز، بین دو معلم، و پس از حذف assignment نوشته/اجرا شوند.
- طرح بصری مرجع روی همه صفحات نقش‌ها اعمال شود؛ این کار باید صفحه‌به‌صفحه با screenshot QA شود.
- این یادداشت گزارشی از ممیزی اولیه است؛ به‌تنهایی به معنی تست یا تغییر عملکرد سیستم نیست.
