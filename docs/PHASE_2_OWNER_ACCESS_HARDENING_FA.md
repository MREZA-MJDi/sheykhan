# فاز ۲ — سخت‌سازی دسترسی و جداسازی داده‌های Owner

**شاخه:** `phase-2/owner-access-isolation`  
**محدودهٔ این PR:** قراردادهای دسترسی Route و جلوگیری از عملیات بین آموزشگاه‌ها  
**هدف:** تغییر Permission یا اضافه شدن Route جدید نباید ناخواسته دسترسی باز ایجاد کند؛ شناسهٔ آموزشگاه در URL نیز نباید اختیار عملیات روی آموزشگاه دیگر را ایجاد کند.

## پیاده‌سازی

فایل `tests/Feature/OwnerAccessIsolationTest.php` قراردادهای عمومی Route و سناریوهای دسترسی بین آموزشگاه‌ها را نگهبانی می‌کند:

1. تمام Routeهای نام‌دار با پیشوند `owner.` باید middleware احراز هویت، فعال‌بودن حساب و Permission داشته باشند. این قاعده شامل فضای SEO هم می‌شود؛ متخصص SEO از مسیر Permission اختصاصی عبور می‌کند و لازم نیست نقش مدیر آموزشگاه داشته باشد.
2. صفحات فهرست چند فضای کاری دارای کنترل مالکیت صریح، با آکادمی متعلق به شخص دیگر فراخوانی می‌شوند. پاسخ باید `403` یا `404` باشد؛ هر دو دسترسی را رد می‌کنند، و `404` برای مسیرهایی که شناسهٔ خارجی را پنهان می‌کنند ترجیح داده می‌شود.
3. تلاش برای باز کردن یا ویرایش کلاس متعلق به آموزشگاه دیگر، با URL آموزشگاهی که مدیر جاری مالک آن است، بررسی می‌شود؛ این سناریو خطر IDOR از طریق شناسهٔ مستقیم رکورد را می‌پوشاند.
4. ثبت اعلان و ثبت سند پرداخت مدرس با آکادمی متعلق به مدیر دیگر آزمایش می‌شود؛ علاوه بر پاسخ `404`، تست بررسی می‌کند رکورد ناخواسته در دیتابیس ساخته نشده باشد.

## فهرست صفحات تحت پوشش

- حضور و غیاب
- اطلاعیه‌ها و ارتباط با خانواده‌ها
- پیام‌های اولیا
- درخواست‌های مشاوره
- آزمون‌ها
- ضبط جلسات
- پرداخت مدرس‌ها
- جزئیات و ویرایش کلاس برای بررسی دسترسی بین آموزشگاه‌ها


## ماتریس Endpoint / Permission / مالکیت در دامنهٔ ممیزی فاز ۲

این ماتریس، قرارداد Route و کنترل رکورد را جدا می‌کند: middleware مجوز عملیات را بررسی می‌کند؛ Controller یا binding باید ثابت کند رکورد متعلق به همان آموزشگاه است. تست سراسری وجود middleware روی همهٔ Routeهای نام‌دار `owner.*` را بررسی می‌کند، اما کنترل مالکیت رکورد برای هر دامنه جداگانه در جدول آمده است.

| Endpoint / Route name | Permission لازم | شرط مالکیت و scope رکورد | Regression guard |
|---|---|---|---|
| `GET owner.attendance.index` | `attendance.view` | مدیر باید مالک Academy باشد؛ فهرست Attendance فقط از Classroomهای همان Academy خوانده می‌شود. | فراخوانی workspace با Academy خارجی باید 404 شود. |
| `PATCH owner.attendance.update` | `attendance.manage` | شناسهٔ Attendance باید به Classroom متعلق به Academy مسیر وصل باشد؛ رکورد قبل از تغییر قفل و دوباره scope می‌شود. | تست رکورد Attendance واقعی از آموزشگاه دوم؛ انتظار 404 و حفظ status/note/marker. |
| `GET owner.communications.index` | `parents.view` | مالکیت Academy و فیلتر پیام‌ها بر اساس `academy_id`; مخاطب خصوصی هم باید عضویت فعال در همان Academy داشته باشد. | workspace خارجی باید با پاسخ `403` یا `404` رد شود. |
| `POST owner.communications.store` | `parents.manage` به‌علاوهٔ بررسی داخلی `parents.view` | مالکیت Academy؛ `academy_id` از پارامتر مجاز سمت سرور گرفته می‌شود؛ دانش‌آموز/والد مقصد باید عضو فعال همان Academy باشند. | ایجاد اعلان برای Academy خارجی باید 404 شود و هیچ رکوردی نوشته نشود. |
| `GET owner.messages.index` | `parents.view` | مالکیت Academy؛ Query پیام‌ها و شمارنده‌ها با `academy_id` همان Academy محدود می‌شود. | workspace خارجی باید با پاسخ `403` یا `404` رد شود. |
| `PATCH owner.messages.reply` | `parents.manage` به‌علاوهٔ بررسی داخلی `parents.view` | پیام هدف باید با `academy_id` همان Academy پیدا شود؛ شناسهٔ مستقیم پیام مجوز دسترسی نیست. | Controller رکورد را با scope آموزشگاه بازیابی می‌کند؛ ماتریس این مسیر را به‌عنوان قرارداد نگهداری می‌کند. |
| `GET owner.counseling.index` | `parents.view` | مالکیت Academy؛ درخواست‌ها و آمار با `academy_id` محدود می‌شوند. | workspace خارجی باید با پاسخ `403` یا `404` رد شود. |
| `PATCH owner.counseling.update` | `parents.manage` به‌علاوهٔ بررسی داخلی `parents.view` | درخواست مشاوره باید متعلق به Academy مسیر باشد؛ جست‌وجوی هدف با `academy_id` و ID ترکیبی انجام می‌شود. | تست درخواست خارجی واقعی؛ انتظار 404 و بدون تغییر status/handler/notes. |
| `GET owner.exams.index/show` | `exams.view` | آزمون باید از طریق Course به Academy مسیر تعلق داشته باشد. | workspace خارجی باید با پاسخ `403` یا `404` رد شود. |
| `GET owner.exams.create`, `POST owner.exams.store`, `GET owner.exams.edit`, `PATCH owner.exams.update/status`, `PATCH owner.exams.attempts.grade` | `exams.manage` | Course هنگام ایجاد از رابطهٔ Academy resolve می‌شود؛ آزمون با `ownedExam()` بر اساس Academy دوره scope می‌شود؛ تلاش دانش‌آموز نیز فقط از آزمون مجاز resolve می‌شود. | تست تغییر وضعیت آزمون واقعی از آموزشگاه دوم؛ انتظار 404 و باقی‌ماندن status. |
| `GET owner.recordings.index` | `live_classes.view` | LiveClassها از طریق Course با `academy_id` محدود می‌شوند. | workspace خارجی باید با پاسخ `403` یا `404` رد شود. |
| `POST owner.recordings.upload`, `PATCH owner.recordings.update` | `live_classes.manage` | LiveClass هدف باید از طریق Course متعلق به Academy باشد؛ بررسی scope قبل از آپلود فایل یا تغییر انتشار انجام می‌شود. | تست انتشار/لغو انتشار جلسهٔ واقعی از آموزشگاه دوم؛ انتظار 404 و حفظ status/visibility/release time. |
| `GET owner.teacher-payments.index` | `teacher_payments.view` | فهرست و جمع مبلغ فقط از `academy_id` همان Academy؛ مدرس‌های مقصد باید عضو فعال همان Academy باشند. | workspace خارجی باید با پاسخ `403` یا `404` رد شود. |
| `POST owner.teacher-payments.store`, `PATCH owner.teacher-payments.status` | `teacher_payments.manage` | مالکیت Academy؛ ثبت سند فقط برای مدرس فعال عضو همان Academy؛ ویرایش سند با `academy_id` و ID محدود می‌شود. | ایجاد پرداخت برای Academy خارجی باید 404 شود و هیچ سندی درج نشود. |
| `GET/PATCH owner.classrooms.show/edit/update` | `classrooms.view` / `classrooms.manage` | `scopeBindings()` باید Classroom را به Academy موجود در URL محدود کند؛ binding رکورد مستقیم، مرز مالکیت را دور نمی‌زند. | تست جزئیات و ویرایش کلاس خارجی از URL آموزشگاه مالک فعلی؛ انتظار 404. |
| `GET owner.orders.index` | `orders.view` | مالکیت Academy و فهرست سفارش‌هایی که محصول یا دورهٔ متعلق به همان Academy دارند. | تست می‌کند سفارش آموزشگاه دوم در فهرست مدیر فعلی نمایش داده نشود. |
| `GET owner.orders.proof` | `orders.view` | سفارش باید به Academy مسیر تعلق داشته باشد؛ پرداخت باید متعلق به همان سفارش باشد؛ فایل رسید باید فعال و خصوصی باشد. | دریافت رسید یک سفارش خارجی باید 404 شود. |
| `POST owner.orders.confirm/reject` | `orders.manage` | مالکیت Academy، تطابق payment.order_id با سفارش، وضعیت قابل بررسی، تطابق مبلغ و رسید خصوصی؛ تغییرات در transaction و با row lock انجام می‌شود. | تأیید/رد سفارش آموزشگاه دوم باید 404 شود؛ سفارش و پرداخت pending بمانند و entitlement ایجاد نشود. |

### نگاشت تست‌ها به ماتریس

- `test_every_owner_route_requires_authentication_active_account_and_a_permission`: قرارداد middleware برای تمام Routeهای نام‌دار `owner.*`.
- `test_unscoped_owner_workspaces_reject_an_academy_owned_by_another_user`: هفت صفحهٔ workspace شامل حضور، ارتباطات، پیام‌ها، مشاوره، آزمون، ضبط و پرداخت مدرس.
- `test_owner_cannot_open_or_edit_a_foreign_classroom_through_an_owned_academy_url`: IDOR در binding کلاس.
- `test_owner_cannot_create_messages_or_teacher_payments_for_a_foreign_academy`: عدم درج پیام و پرداخت با Academy خارجی.
- `test_owner_cannot_review_an_order_payment_from_another_academy`: عدم نمایش سفارش خارجی و رد دریافت رسید/تأیید/رد پرداخت بدون ایجاد دسترسی خرید.
- تست‌های جدید attendance، counseling، exam status و recording publication: عملیات تغییردهنده با رکورد واقعی از Academy دوم و URL حاوی Academy متعلق به کاربر جاری.

## اجرای بررسی‌ها

در CI باید تست جدید همراه با suite موجود اجرا شود:

```bash
php artisan test --filter=OwnerAccessIsolationTest
php artisan test
npm run build
```

تست‌های این PR قراردادهای سطح Route و چند نقطهٔ حساس ایجاد داده را نگهبانی می‌کنند؛ جایگزین بازبینی هر Controller، Policy، FormRequest یا لاگ محیط Production نیستند. نتیجهٔ CI باید سبز شود و برای تأیید نهایی امنیت، smoke-test با دو حساب مدیر و دو آموزشگاه مستقل در محیط deploy‌شده اجرا شود.

## خارج از محدودهٔ این PR

- تغییر سیاست‌های تجاری یا نقش‌های موجود
- تغییر Schema دیتابیس
- ادعای اثبات امنیت تمام ۹۶ Route مدیریتی یا همهٔ مدل‌ها
- ادعای deploy یا تأیید محیط زنده

پس از افزودن تست‌های نوشتن بین آموزشگاه‌ها، گیت‌های باقی‌مانده برای بستن انتشار عبارت‌اند از سبزشدن CI و smoke-test دستی با دو حساب مالک و دو آموزشگاه مستقل در محیط deploy‌شده. این شاخه به‌تنهایی تأیید Production یا انجام deploy را ثابت نمی‌کند.
