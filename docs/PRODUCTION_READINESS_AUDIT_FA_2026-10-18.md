# ممیزی آمادگی تولید شیخان — 2026-10-18

## مرجع ممیزی

- مخزن: `MREZA-MJDi/sheykhan`
- شاخه مبنا برای این فاز: `feat/public-academy-experience-2026-10-17`
- Commit مبنا: `fef27ed23539011671e7203a641bd75ed79b5cee`
- شاخه اصلی فعلی در GitHub: `main` با HEAD برابر `ec5188fdde876a6cbccf69ddea0e882626742ae0`؛ بنابراین نسخه‌ای که ممیزی شده هنوز همان HEAD شاخه اصلی نیست.
- آخرین اجرای قابل مشاهده‌ی workflow تست روی commit مبنا موفق بوده است: [Run 39063738549](https://github.com/MREZA-MJDi/sheykhan/actions/runs/39063738549) و [Run 39063529967](https://github.com/MREZA-MJDi/sheykhan/actions/runs/39063529967). این نتیجه، اثبات اجرای workflow جداگانه‌ی Readiness روی SQLite و MariaDB یا تست پس از استقرار نیست.

## یافته‌های فنی قابل اندازه‌گیری

### 1) CSS و لایه‌لایه شدن UI — اولویت بالا

اندازه‌ها از فایل‌های شاخه مبنا خوانده شده‌اند. شمار تکرار selectorها یک شاخص ممیزی ایستا است؛ همه تکرارها الزاماً قابل حذف نیستند، چون برخی در breakpointها یا `prefers-reduced-motion` رفتار متفاوتی دارند.

| فایل | حجم تقریبی | خط | گروه selector تکراری |
|---|---:|---:|---:|
| `resources/css/home.css` | 114 KB | 4,754 | 161 |
| `resources/css/student.css` | 126 KB | 4,389 | 192 |
| `resources/css/owner.css` | 78 KB | 1,112 | 120 |
| `resources/css/teacher.css` | 47 KB | 1,050 | 101 |
| `resources/css/public-experience.css` | 42 KB | 846 | 58 |

نمونه‌های تکرار در `home.css`: `.home-path-grid a` سیزده بار، `.home-path-grid` نه بار، `.home-hero-grid` پنج بار و `.home-academy-story__visual` پنج بار تعریف شده‌اند. در `student.css`، selectorهای `.student-ui-hero` و `.student-ui-quick-grid` هرکدام هشت بار آمده‌اند.

اقدام درست: قبل از حذف، هر selector بر اساس scope و media query دسته‌بندی شود؛ برای هر کامپوننت یک تعریف پایه و فقط overrideهای responsive/حالت تعاملی لازم باقی بماند. حذف دسته‌جمعی با جست‌وجوی متنی می‌تواند ظاهر موبایل، دسترس‌پذیری یا حالت reduced-motion را بشکند و پذیرفته نیست.

### 2) مرزبندی MVC و سرویس‌ها — اولویت بالا

- `routes/web.php` حدود 31 KB، دارای 283 خط فیزیکی و 164 ثبت route است. routeها در چند گروه middleware قرار دارند اما مرز عمومی، تجاری و پنل‌ها هنوز در یک فایل مرکزی جمع شده است.
- کنترلرهای مالک، دانش‌آموز، مدرس، والدین و سایت عمومی پوشه‌های مجزا دارند و لایه Service هم وجود دارد؛ بنابراین لازم نیست معماری موازی جدیدی ساخته شود.
- چند سرویس پرمسئولیت هستند: `StudentDashboardService` حدود 587 خط، `TeacherWorkspaceService` حدود 469 خط و `OwnerDashboardService` حدود 400 خط. به‌خصوص سرویس مدرس هم فهرست‌خوانی، scope دسترسی و تعدادی عملیات نوشتن را پوشش می‌دهد. شکستن این سرویس‌ها باید بر اساس use case و بدون تغییر قراردادهای route/response انجام شود، نه با ساختن abstractionهای عمومی و کم‌مصرف.
- `TeacherWorkspaceNPlusOneTest` وجود دارد؛ این پوشش باید حفظ شود و برای داشبوردهای دیگر هم تست رگرسیون query اضافه شود. در این مرحله تعداد واقعی queryهای runtime یا latency تولید اندازه‌گیری نشده است و نباید برای آن عدد ادعا کرد.

### 3) سئو و نقش متخصص — بخشی از تفکیک از قبل موجود است

در نسخه ممیزی‌شده نقش `seo-specialist`، مجوز `seo.manage`، دستور `sheykhan:seo-access` و تست‌های جداسازی دسترسی موجودند. دستور دسترسی فقط نقش محدودِ `dashboard.view` و `seo.manage` را همگام می‌کند و اکانت جدید نمی‌سازد. تست‌ها دسترسی متخصص به داشبورد مالک و تنظیمات وب‌سایت را رد می‌کنند.

با این حال، رابط متخصص هنوز از `Owner\\SeoController`، نام routeهای `owner.seo.*`، مسیرهای `owner/seo` و layout مالک استفاده می‌کند. پس جداسازی سطح مجوز تا حد زیادی انجام شده، اما جداسازی UI/route/layout کامل نیست. فاز سئو باید همین قابلیت را به یک فضای مستقل با مسیر، layout، navigation و مجوزهای روشن منتقل کند و قراردادهای SEO عمومی، sitemap، robots، canonical، noindex و cache invalidation را با تست حفظ کند؛ نباید صفحه سئوی دوم کنار قبلی ساخته شود.

### 4) CI و تکرارپذیری build — اصلاح لازم

در `.github/workflows/tests.yml` مرحله‌ای وجود داشت که حین CI دستور `npm audit fix --package-lock-only` را اجرا می‌کرد، سپس lockfile تغییرکرده را نصب و build می‌کرد و به‌عنوان artifact تحویل می‌داد. این یعنی dependency graph تست‌شده لزوماً همان چیزی نیست که با `npm ci` از lockfile commitشده در استقرار نصب می‌شود. این رفتار باید حذف شود؛ CI باید دقیقاً با lockfile commitشده نصب و build کند و audit وابستگی‌ها را جداگانه گزارش دهد.

Workflow جداگانه‌ی `.github/workflows/readiness.yml` از قبل build و تست PHP را روی SQLite و MariaDB تعریف کرده است؛ باید مطمئن شویم در PR نهایی واقعاً اجرا شده و موفق است.

### 5) استقرار واقعی — هنوز نیازمند تأیید عملیاتی

Workflow استقرار فقط از `main` و با تأیید دستی اجرا می‌شود و secrets مربوط به SSH و مسیر سرور را لازم دارد. مستندات پروژه هم تصریح می‌کنند که backupهای ساخته‌شده روی همان دیسک سرور به‌تنهایی disaster recovery نیستند، پرداخت واقعی بدون gateway/callback معتبر آماده نیست و post-deploy smoke test لازم است. وضعیت secrets، سرور، TLS، backup خارج از سرور و restore واقعی از این ممیزی قابل تأیید نیست.

همچنین در وضعیت فعلی شاخه `main` محافظت‌شده نیست و required status check اجباری ثبت نشده است. قبل از release باید حفاظت شاخه و اجبار CI در تنظیمات GitHub فعال شود.

## برنامه اصلاح با دروازه پذیرش

1. **Baseline قطعی:** build با lockfile commitشده، lint PHP، migrate:fresh و کل تست‌ها روی SQLite و MariaDB؛ شکست‌ها دسته‌بندی شوند، نه با fallback پوشانده شوند.
2. **معماری و قراردادها:** ثبت نقشه‌ی route→middleware→controller→request→service→view، جداکردن route fileها بر اساس سطح دسترسی، کوتاه‌کردن سرویس‌های پرمسئولیت بر مبنای use case و حفظ نام route/response فعلی با تست.
3. **چهار پنل کاربردی:** مالک/مدیر، دانش‌آموز، مدرس و والدین؛ تست هر تب و workflow، scope داده بین کاربران/آموزشگاه‌ها، pagination، N+1، ورودی‌های نامعتبر، خطاهای 403/404/422 و alertهای قابل‌دسترس.
4. **UI/CSS:** تعیین فایل canonical و design tokenهای مشترک؛ ادغام تکرارها با بررسی cascade و breakpoint، حذف استایل‌های واقعاً بی‌استفاده پس از جست‌وجوی مصرف، و بارگذاری هر bundle فقط برای surface لازم. هیچ CSS override جدیدی صرفاً برای پوشاندن CSS قبلی اضافه نشود.
5. **SEO مستقل:** منتقل‌کردن UI و مسیر متخصص به فضای مستقل با least privilege؛ حفظ مدیریت metadata برای مالک آموزشگاه و متخصص با scope متفاوت؛ تست sitemap/robots/canonical/schema/noindex و پاک‌سازی cache پس از update.
6. **Production gate:** branch protection و required checks، secrets سرور، TLS و تنظیمات production، backup رمزگذاری‌شده خارج از سرور با restore آزمایشی، queue/scheduler/mail/monitoring، smoke test پس از deploy و برنامه rollback بررسی‌شده.

## تعریف «آماده استقرار»

نسخه فقط وقتی آماده release است که CI کامل روی همان SHA سبز باشد، تمام چهار پنل و نقش سئو به‌صورت مجاز/غیرمجاز تست شده باشند، build از lockfile قطعی انجام شود، خطا/کوئری پرتکرار بدون عددسازی اندازه‌گیری شود، backup قابل restore موجود باشد و smoke test روی محیط هدف موفق شود. «کد در شاخه موجود است» یا «یک workflow تست موفق شده» معادل استقرار تأییدشده نیست.
