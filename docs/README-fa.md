# مستندات شیخان

این پوشه مرجع کار تیم محصول، توسعه و تست است. مستندات قابلیت‌ها بر اساس Routeها، مدل‌ها، Serviceها، Viewها، Permissionها و تست‌های موجود نوشته می‌شوند. هر جا اتصال خارجی یا workflow کامل نیست، به‌عنوان محدودیت ذکر می‌شود.

## از کدام سند شروع کنم؟

| نیاز | سند |
|---|---|
| مرور کلی قابلیت‌ها برای محصول، پشتیبانی یا همکار جدید | [کاتالوگ کامل قابلیت‌ها](FEATURE_CATALOG_FA.md) |
| فهم روشن هر داشبورد، فرایند دانش‌آموز پایه دهم/استاد چنددوره‌ای و ممیزی دسترسی مدرس | [راهنمای مسیرها، منطق داشبورد و import](PLATFORM_DASHBOARD_AND_LEGACY_IMPORT_FA.md) |
| ساخت جلسه آنلاین، فرق دوره/گروه/برنامه هفتگی/جلسه تاریخ‌دار، لینک provider | [راهنمای کلاس آنلاین و برنامه هفتگی](ONLINE_CLASSES_AND_SCHEDULES_FA.md) |
| URLها، Route nameها، نقش‌ها و Permissionها | [مرجع Route و دسترسی](ROUTES_AND_PERMISSIONS_FA.md) |
| سخت‌سازی دسترسی Owner و جداسازی داده‌های آموزشگاه‌ها (فاز ۲) | [گزارش فاز ۲ — امنیت دسترسی Owner](PHASE_2_OWNER_ACCESS_HARDENING_FA.md) |
| مرز مسئولیت نقش‌ها و گردش اصلی سیستم | [Workflow پلتفرم](platform-workflow.md) |
| جدول‌ها، رابطه‌ها و تصمیم‌های دیتابیس | [طرح دیتابیس](database-schema.md) |
| استقرار Production | [راهنمای استقرار](production-deployment.md) |
| ورود امن دانش‌آموزهای قدیمی WordPress | [راهنمای Import دانش‌آموزها](LEGACY_STUDENT_IMPORT_FA.md) |
| اتصال و تست SpotPlayer، تفکیک API key/course ID/license key | [راهنمای عملی SpotPlayer](SPOTPLAYER_INTEGRATION_FA.md) |
| استقرار سرور sheykhan روی پورت 8082 | [راهنمای VPS](vps-sheykhan-8082.md) |
| گردش کار Cowork/Claude | [راهنمای Cowork](claude-cowork-workflow.md) |
| مدیریت SEO و فهرست کلمات کلیدی | [راهنمای پنل SEO و کلمات کلیدی](SEO_KEYWORD_WORKSPACE_FA.md) |

## قرارداد مستندسازی

- «پیاده‌سازی شده» یعنی در کد route/controller/service/view وجود دارد؛ «کارکرده در Production» را فقط پس از Deploy و تست دستی می‌توان تأیید کرد.
- برای قابلیت دارای تست، فایل تست در سند مشخص می‌شود.
- انتخاب provider در فرم تماس، به‌تنهایی یک جلسه واقعی در سرویس بیرونی ایجاد نمی‌کند. Jitsi با نام اتاق یکتا کار می‌کند؛ Meet و Zoom تا زمان اضافه شدن اتصال رسمی API، نیاز به لینک واقعی ساخته‌شده در همان سرویس دارند.
- از route nameهای Laravel استفاده کنید؛ URL را در چند View به‌صورت دستی تکرار نکنید.
- تغییر permission باید با تست عدم دسترسی غیرمجاز همراه باشد.
- مستندات، جای تست کد یا مشاهدهٔ لاگ Production را نمی‌گیرند.

## چک‌لیست پیش از PR

1. Routeها و middleware را بررسی کنید.
2. Controller/Request/Service/Model و scope مالکیت را دنبال کنید.
3. تست Feature برای موفقیت، ورودی نامعتبر و دسترسی غیرمجاز بنویسید.
4. php artisan test و npm run build را اجرا کنید.
5. در صورت تغییر Schema، migration جدید و قابل rollback اضافه کنید.
6. محدودیت‌های provider بیرونی، storage یا secretها را صریح بنویسید.
