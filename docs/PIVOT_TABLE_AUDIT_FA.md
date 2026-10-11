# ممیزی Pivot Tableها و روابط چندبه‌چند — آکادمی شیخان

> وضعیت این سند: ممیزی کد و مایگریشن‌های شاخه `main`، نه ممیزی مستقیم ردیف‌های دیتابیس Production. برای بررسی دادهٔ زنده باید خروجی Queryهای فقط‌خواندنی انتهای سند از دیتابیس واقعی گرفته شود. هیچ داده‌ای در Production تغییر داده نشده است.

## قواعد اجرای امن در Production

1. هر pivot را با کلید رابطهٔ واقعی‌اش بررسی کنید؛ در `academy_user` این کلید شامل نقش هم هست.
2. حذف رابطه با تاریخچهٔ آموزشی را با `detach()` انجام ندهید؛ وضعیت را به یک حالت غیر فعال/انتقالی تغییر دهید، مگر اینکه محصول صراحتاً حذف دائمی را تعریف کرده باشد.
3. قبل از افزودن unique index، ابتدا تکراری‌های موجود را با Query فقط‌خواندنی شناسایی و گزارش کنید؛ مایگریشن نباید خودش رکوردهای زنده را حدس بزند یا حذف کند.
4. تغییرات schema و داده جداگانه انجام شوند؛ استقرار کد مجوز اجرای seeder روی Production نیست.

## موجودی و وضعیت ممیزی

| جدول رابطه | قید فعلی | وضعیت و رفتار مورد انتظار |
|---|---|---|
| `role_user` | کلید اصلی مرکب `(role_id,user_id)` و FKهای cascade | از نظر تکرار رابطه مناسب است. عملیات باید از `User::roles()` / `Role::users()` انجام شود؛ نقش سراسری با عضویت آکادمی یک مفهوم جداست. |
| `permission_role` | کلید اصلی مرکب `(permission_id,role_id)` | قید تکرار مناسب است. تغییر دسترسی باید از مسیر مدیریت نقش انجام شود؛ حذف Role/Permission به‌صورت cascade مجاز است، ولی باید تصمیم مدیریتی باشد. |
| `academy_user` | یکتایی `(academy_id,user_id,role)` | **ریسک بالا:** چون یک کاربر می‌تواند در یک آکادمی بیش از یک نقش داشته باشد، نوشتن از رابطهٔ عمومی `users()` بر اساس `user_id` کافی نیست. ایجاد/آرشیو/بازیابی باید از رابطهٔ role-scoped مثل `teachers()` یا `students()` استفاده کند. در شاخهٔ ممیزی، مسیرهای ایجاد و آرشیو/بازیابی مدرس به رابطهٔ مدرس محدود شده‌اند؛ مسیر onboarding دانش‌آموز هنوز نیازمند همین اصلاح است. |
| `parent_student` | کلید اصلی مرکب `(parent_id,student_id)`، timestamps | تکرار همان والد/دانش‌آموز بسته شده است. قبل از اتصال باید نوع حساب و مجوز سروری والد و دانش‌آموز اعتبارسنجی شود؛ مقدار `relation` دادهٔ pivot است و از رابطهٔ اشتباه نباید بازنویسی شود. |
| `course_teacher` | کلید اصلی مرکب `(course_id,teacher_id)` | اتصال تکراری بسته شده و سرویس‌های مدیریت کلاس، مدرس را با عضویت آکادمی و تخصیص دوره اعتبارسنجی می‌کنند. **مورد باز:** `is_primary` فعلاً اجازه می‌دهد بیش از یک مدرس یک دوره را primary کند؛ باید سیاست انتخاب یک مدرس اصلی، در سرویس و سپس در صورت امکان DB enforce شود. |
| `classroom_teacher` | کلید اصلی مرکب `(classroom_id,teacher_id)` | تکرار بسته است. هنگام sync باید لیست مدرس‌ها از نظر آکادمی فعال و تخصیص به همان دوره بررسی شود؛ این اعتبارسنجی در `OwnerClassroomService` وجود دارد. |
| `classroom_student` | کلید اصلی مرکب `(classroom_id,student_id)`؛ وضعیت و زمان ثبت‌نام | **ریسک بالا:** `detach()` هنگام انتقال دانش‌آموز، ردیف عضویت قبلی را حذف می‌کرد. در شاخهٔ ممیزی این مسیر به تغییر وضعیت عضویت فعال قبلی به `transferred` تغییر کرده تا سابقهٔ roster حفظ شود. فعال بودن ظرفیت و فهرست کلاس همچنان بر اساس `status=active` است. |
| `course_grade` | کلید اصلی مرکب `(course_id,grade_id)` | قید تکرار مناسب است. |
| `blog_post_tag` | کلید اصلی مرکب `(blog_post_id,blog_tag_id)` و timestamps | قید تکرار مناسب است؛ تغییر برچسب‌ها از رابطهٔ `tags()` انجام شود. |
| `media_attachments` | FK روی `media_id`، ایندکس morph و unique روی `(media_id,mediable_type,mediable_id,collection)` | **نیاز به تصمیم طراحی:** رابطهٔ Eloquent با `syncWithoutDetaching` رسانه را با شناسهٔ `media_id` می‌شناسد، در حالی که unique دیتابیس اجازهٔ اتصال همان رسانه به همان مقصد در چند collection را می‌دهد. یا باید اتصال برای هر رسانه/مقصد یکتا باشد، یا عملیات collection-aware مستقیماً با مدل `MediaAttachment` نوشته شود. پیش از migration باید داده‌های موجود بررسی شوند. |
| `product_files` | `unique(product_id,media_id)` | این یک جدول فرزند است، نه pivot کلاسیک. **مورد باز:** ایندکس فعلی چند فایل `is_primary=true` را برای یک محصول منع نمی‌کند؛ سرویس باید primary را اتمیک و تک‌گزینه‌ای کند و سپس constraint متناسب با DB انتخاب شود. |
| `product_entitlements` | `order_item_id` یکتا | درست است که یک آیتم سفارش entitlement یکتا بسازد؛ چند entitlement برای یک کاربر/محصول می‌تواند ناشی از خریدهای جداگانه باشد و نباید بدون سیاست کسب‌وکار ادغام شود. |
| `course_enrollments` | موجودیت دارای وضعیت/مبالغ/سال تحصیلی؛ یکتایی بر اساس دوره، دانش‌آموز و کلید مؤثر سال | این جدول pivot ساده نیست و باید جدا ممیزی شود. سرویس‌های ثبت‌نامی که فقط با `course_id + student_id` جست‌وجو می‌کنند، باید در برابر تاریخچهٔ سال تحصیلی بازبینی شوند؛ سال کلاس یا سال جاری باید صریح باشد و تاریخچهٔ سال قبل بازنویسی نشود. |

## اصلاحات انجام‌شده در شاخهٔ ممیزی

- رابطه‌های role-scoped آموزشگاه، timestamps را هم مدیریت می‌کنند.
- ایجاد مدرس و آرشیو/بازیابی عضویت مدرس از رابطهٔ `teachers()` عبور می‌کند؛ وضعیت نقش‌های دیگر کاربر نباید تغییر کند.
- انتقال roster کلاس، عضویت قبلی را به‌جای حذف فیزیکی، با وضعیت `transferred` حفظ می‌کند.
- تست lifecycle مدرس طوری گسترش داده شده که همان کاربر هم‌زمان ردیف `teacher` و `student` داشته باشد و عملیات مدرس حق تغییر ردیف دانش‌آموز را نداشته باشد.

## Queryهای فقط‌خواندنی پیشنهادی برای Production

این Queryها فقط داده می‌خوانند؛ قبل از اجرا نام دیتابیس/موتور و دسترسی read-only بررسی شود.

### چند نقش برای یک کاربر در یک آکادمی
این خروجی لزوماً خطا نیست؛ برای یافتن حساب‌هایی است که چند عضویت دارند و عملیات role-scoped آن‌ها باید درست باشد.

```sql
SELECT academy_id, user_id,
       COUNT(DISTINCT role) AS role_count,
       GROUP_CONCAT(DISTINCT role ORDER BY role) AS roles
FROM academy_user
GROUP BY academy_id, user_id
HAVING COUNT(DISTINCT role) > 1;
```

### چند مدرس اصلی برای یک دوره
```sql
SELECT course_id, COUNT(*) AS primary_count
FROM course_teacher
WHERE is_primary = 1
GROUP BY course_id
HAVING COUNT(*) > 1;
```

### چند فایل اصلی برای یک محصول
```sql
SELECT product_id, COUNT(*) AS primary_count
FROM product_files
WHERE is_primary = 1
GROUP BY product_id
HAVING COUNT(*) > 1;
```

### تعداد ردیف‌ها در pivotهای مهم
```sql
SELECT 'academy_user' AS table_name, COUNT(*) AS row_count FROM academy_user
UNION ALL SELECT 'course_teacher', COUNT(*) FROM course_teacher
UNION ALL SELECT 'classroom_teacher', COUNT(*) FROM classroom_teacher
UNION ALL SELECT 'classroom_student', COUNT(*) FROM classroom_student
UNION ALL SELECT 'parent_student', COUNT(*) FROM parent_student
UNION ALL SELECT 'media_attachments', COUNT(*) FROM media_attachments;
```

## ترتیب اجرای پیشنهادی

1. **Batch 1 — دسترسی/عضویت:** تکمیل همهٔ write pathهای `academy_user` و تست نقش‌های هم‌زمان.
2. **Batch 2 — cohort/history:** roster انتقالی/غیرفعال، ثبت‌نام سال‌تحصیلی و رفتار بازگشت به کلاس؛ بدون پاک‌کردن سوابق.
3. **Batch 3 — روابط محتوا:** تصمیم قطعی دربارهٔ primary teacher، primary product file، و semantics رسانه/collection؛ سپس migration سازگار با دادهٔ موجود.
4. **Batch 4 — Production verification:** اجرای Queryهای read-only، backup پیش از هر migration داده‌ای، انتشار مرحله‌ای، و بررسی log/داشبورد پس از انتشار.

هیچ‌یک از Queryها یا اصلاحات این شاخه به‌معنی تأیید سلامت ردیف‌های فعلی Production نیست؛ این کار نیازمند مشاهدهٔ خروجی واقعی دیتابیس است.
