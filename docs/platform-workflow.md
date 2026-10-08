# شیخان — Workflow و مرز مسئولیت نقش‌ها

این سند قرارداد اجرایی فعلی پلتفرم است؛ UI، Controller، Service و Query باید با این flow هماهنگ بمانند.

## 1. مسیر اصلی سیستم

Public → دوره/مقاله/محتوای آکادمی → ثبت‌نام یا پرداخت → CourseEnrollment → دسترسی دانش‌آموز → درس و LessonProgress / تکلیف / آزمون / کلاس آنلاین → نتیجه و سابقه آموزشی

Academy Owner → Academy → Course → Teacher assignment → Classroom → Student enrollment → نظارت روی attendance / progress / pending work / finance / public content / SEO

Teacher → فقط Courseهای assign‌شده در Academy فعال → Classroomهای خودش → Lesson / Assignment / Exam / Attendance / LiveClass → مشاهده و ارزیابی دانش‌آموزان همان scope

Student → فقط Enrollment و Classroom فعال خودش → Course / Lesson / Assignment / Exam / LiveClass → Note / Resource / Result / Achievement / Profile

## 2. قانون اصلی دسترسی

هیچ نقش نباید صرفاً با دانستن ID یا URL به داده دسترسی بگیرد.

- Owner باید مالک Academy باشد.
- Course Owner باید Course متعلق به Academy مالک باشد.
- Teacher باید هم course_teacher داشته باشد و هم academy_user فعال با نقش teacher.
- Classroom Teacher باید classroom_teacher داشته باشد و membership آموزشگاه فعال باشد.
- Student باید Academy membership فعال و Enrollment معتبر داشته باشد.
- Paid Course فقط با enrollment فعال، پرداخت کامل و evidence مالی معتبر unlock می‌شود.

## 3. مرز Owner

Owner «مدیر و ناظر» است، نه مدرس اجرایی.

مجاز:
- مدیریت Academy
- مدیریت Course و Media
- مدیریت Teacher / Student / Parent در scope آموزشگاه
- مدیریت Classroom
- گزارش عملکرد
- کنترل سایت و Banner
- تولید محتوای Academy
- مقاله‌های متعلق به خودش
- SEO برای Academy، Course، Academy Content و Blog Post خودش
- مشاهده مالی به‌صورت read-only

نامجاز:
- تصحیح تکلیف یا آزمون به جای Teacher
- ثبت attendance به جای Teacher
- ورود به داده آموزشگاه دیگر
- تغییر مستقیم ledger مالی

## 4. انتشار عمومی و SEO

هر محتوای قابل ایندکس باید رکورد دیتابیس داشته باشد و public URL پایدار داشته باشد.

Public HTML باید در head شامل موارد زیر باشد:
- title
- description
- robots
- canonical
- OpenGraph
- در صورت تنظیم schema JSON

/robots.txt و /sitemap.xml باید endpoint واقعی باشند.

SEO هیچ‌گاه به معنی تضمین رتبه نیست. تست automated فقط بررسی می‌کند crawler-facing output واقعاً وجود دارد؛ Indexing/ranking واقعی بعد از استقرار دامنه از Google Search Console قابل مشاهده است.

## 5. Media

فایل اصلی نباید برای crop دوباره encode شود مگر اینکه واقعاً لازم باشد.

برای Banner:
- فایل اصلی دست‌نخورده می‌ماند.
- crop_x و crop_y فقط focal position نمایش را کنترل می‌کنند.
- Preview سمت Client قبل از submit انجام می‌شود.
- خروجی عمومی همان فایل اصلی است.

## 6. Finance

FinancialTransaction منبع ledger است.

گزارش Owner:
- از transactionهای completed می‌خواند.
- enrollment payment را income می‌کند.
- refund را از net کم می‌کند.
- به transaction جدید یا تغییر دلخواه ledger دسترسی نوشتن نمی‌دهد.

## 7. Performance

اصل query:
- eager loading برای روابط نمایش
- withCount به جای loop query
- pagination برای لیست‌ها
- aggregation با DB برای KPI
- cache برای public home data
- جلوگیری از N+1 در sitemap و dashboard

## 8. UX قرارداد نقش‌ها

Student:
- اولویت با «قدم بعدی» و ادامه یادگیری
- CTA کم ولی واضح
- mobile bottom navigation برای کارهای پرتکرار
- تقویم، تکلیف، کلاس و نتیجه بدون شلوغی

Teacher:
- اولویت با جلسه امروز، کارهای نیازمند بررسی و کلاس/دانش‌آموز
- دسترسی سریع به attendance / assignment / exam
- فرم‌های Persian-first و Jalali-friendly
- mobile bottom navigation

Owner:
- اولویت با وضعیت کلان، هشدارها، عملکرد، سایت، SEO و مالی
- UI مدیریتی روشن و متراکم ولی قابل اسکن
- read-only بودن بخش‌های نظارتی تا حد امکان

## 9. تست‌های حداقلی قبل از انتشار

- Owner cross-academy access → 403/404
- Teacher archived membership → no classroom/course/grading access
- Student foreign course/lesson/assignment/exam → 404
- Paid course before full payment → no protected access
- Content publish → public page + sitemap
- SEO update → public HTML head changes
- robots/sitemap → valid public endpoints
- Banner crop → only focal position changes
- Mobile role navigation → route/permission contract preserved