# ممیزی مسیرهای داشبورد شیخان و پیام‌رسانی دوطرفه

## هدف
ساخت یک Design System واحد برای پنل دانش‌آموز و معلم، با حفظ معماری Laravel و تفکیک داده‌ها و مجوزهای هر نقش. هر صفحه باید از نظر URL، داده، اکشن، پاسخ موفق/خطا و مسیر برگشت قابل اعتماد باشد.

## وضعیت فنی موجود
- Laravel 12، Blade و Vite.
- Layoutها جدا هستند: `resources/views/layouts/student.blade.php` و `resources/views/layouts/teacher.blade.php`.
- CSS مشترک موجود است: `resources/css/panel-base.css` و `resources/css/components.css`.
- CSSهای نقش/صفحه نیز موجودند: `student.css`, `teacher.css`, `student-dashboard-reference.css`, `teacher-workspace.css`, `homeroom-dashboard-theme.css`. مشکل فقط «دو فایل برای هر نقش» نیست؛ cascade و مسئولیت هر فایل باید روشن و تکرارها حذف شوند، نه اینکه بی‌بررسی فایل‌ها حذف شوند.
- داشبورد دانش‌آموز کلاس‌های `student-dashboard-reference.css` را برای طرح خاص خودش استفاده می‌کند؛ باید به Design System مرجع منتقل شود، نه اینکه رفتار یا داده‌های داشبورد از نو ساخته شود.
- داشبورد معلم داده‌های واقعی مانند کلاس‌های فعال، تعداد دانش‌آموز، جلسات و کارهای منتظر بررسی را نمایش می‌دهد؛ KPIها و لینک‌ها باید با مجوز و داده واقعی سازگار بمانند.

## مسیرهای Teacher که در routes/web.php ثبت شده‌اند
گروه middleware: `auth`, `active`, `role:teacher`, `active-teacher`; بیشتر عملیات‌ها permission middleware دارند.

| بخش | نام مسیرها / اکشن‌ها | داده و رفتار مورد انتظار |
|---|---|---|
| خانه | `teacher.dashboard` | آمار واقعی کلاس‌ها، دانش‌آموزان، جلسات و کارهای منتظر بررسی؛ CTAها به صفحه مرتبط |
| پروفایل | `teacher.profile.edit/update` | اطلاعات شخصی، اعتبارسنجی، پیام موفقیت و خطا |
| دوره‌ها | `teacher.courses.index/create/store/edit/update` | فهرست دوره‌های قابل مدیریت، فرم ساخت/ویرایش و خطاهای validation |
| رسانه دوره | `teacher.courses.media.store/destroy` | فقط فایل متعلق به دوره مجاز، وضعیت آپلود و خطای نوع/حجم |
| محتوای درس | `teacher.courses.content`, `teacher.courses.sections.store`, `teacher.lessons.store/update`, `teacher.lessons.media.store` | بخش‌ها، درس‌ها، رسانه‌ها، ترتیب و مجوز ویرایش |
| پیشرفت دوره | `teacher.courses.progress` | پیشرفت دانش‌آموزان همان دوره و داده صفحه‌بندی/فیلتر |
| کلاس‌ها | `teacher.classrooms.index/create/store` | فقط کلاس‌های مربوط به مدرس و فرم ایجاد مجاز |
| دانش‌آموزان | `teacher.students.index` | فقط دانش‌آموزان کلاس‌های مدرس؛ مرتب‌سازی معتبر و صفحه‌بندی |
| حضور و غیاب | `teacher.attendance.index`, `teacher.classrooms.attendance.edit/store` | تاریخ/کلاس، فهرست اعضای همان کلاس، ثبت و نمایش نتیجه |
| برنامه | `teacher.schedule.index/store/destroy` | برنامه معتبر، جلوگیری از داده تکراری/تداخل و امکان حذف مجاز |
| تکالیف | `teacher.assignments.index/create/store/submissions/submissions.update` | ایجاد تکلیف، مشاهده تحویل‌های مربوط و بازخورد/نمره |
| آزمون‌ها | `teacher.exams.index/create/store/attempts`, `teacher.exam-attempts.grade/grade-manual` | آزمون‌های مدرس، تلاش‌های مجاز، تصحیح و نمایش وضعیت ذخیره |
| پیام والد | `teacher.messages.index/reply` | فقط پیام‌های مرتبط و پاسخ قابل مشاهده برای فرستنده |
| پیام مستقیم | `teacher.direct-messages.index/store/reply` | مخاطب مجاز، thread صحیح و مشاهده پیام/پاسخ توسط دو طرف |
| پیام دانش‌آموز | `teacher.student-messages.index/reply/close` | فقط دانش‌آموز مرتبط با کلاس/دوره فعال؛ پاسخ و بستن گفت‌وگو |
| اطلاعیه | `teacher.communications.index/store` | مخاطبان و کلاس هدف واضح؛ تفکیک از پیام خصوصی |
| جلسه/قرار | `teacher.meetings.index/proposals.store/status` | درخواست جلسه، وضعیت آن و بازتاب تغییر وضعیت در طرف دیگر |
| کلاس زنده | `teacher.live-classes.index/create/store/recording.store` | زمان، لینک ورود، ضبط و خطاهای آپلود |
| پرداخت | `teacher.payments.index` | فقط داده‌های پرداخت مجاز و وضعیت روشن |

## مسیرهای Student که در routes/web.php ثبت شده‌اند
گروه middleware: `auth`, `active`, `role:student`; عملیات‌های اصلی دارای permission middleware هستند.

| بخش | نام مسیرها / اکشن‌ها | داده و رفتار مورد انتظار |
|---|---|---|
| خانه | `student.dashboard` | دوره‌ها، پیشرفت، تکالیف، نتایج، کلاس‌های آینده و لینک‌های مجاز؛ همه KPIها از داده واقعی |
| پیام‌ها | `student.messages.index/store/teacher-messages.reply` | فقط معلمان مرتبط؛ پیام ارسالی و پاسخ معلم در همان thread قابل دیدن |
| دوره‌ها | `student.courses.index/show` | فقط دوره‌های ثبت‌نام‌شده و مجاز |
| درس | `student.lessons.show/progress.update` | محتوای درس مجاز و ذخیره پیشرفت واقعی |
| تکلیف | `student.assignments.index/show/submit` | موعد، وضعیت ارسال، تأیید ارسال، خطا و بازخورد |
| آزمون | `student.exams.index/show/start/attempt/result`, `student.exam-attempts.submit` | فقط آزمون منتشرشده/مجاز، محدودیت شروع و ارسال، نتیجه پس از مجاز شدن |
| کلاس زنده | `student.live-classes.index/join`, `student.schedule.join` | فقط جلسه مرتبط با دانش‌آموز، زمان و وضعیت درست |
| حضور و غیاب | `student.attendance.index` | سابقه خود دانش‌آموز، فیلتر زمانی و empty state |
| نتایج | `student.results.index` | نمرات و بازخوردهای منتشرشده، بدون افشای نتایج دیگران |
| دستاوردها | `student.achievements.index` | دستاوردهای خود دانش‌آموز و empty state |
| یادداشت‌ها | `student.notes.index`, `student.lessons.notes.store`, `student.notes.destroy` | CRUD یادداشت‌های شخصی فقط متعلق به کاربر جاری |
| پروفایل | `student.profile.edit/update`, `student.profile.password.update` | ویرایش، اعتبارسنجی و پیام موفقیت/خطا |
| منابع | `student.resources.index/view/download` | فقط منابع متعلق به دوره‌های مجاز؛ خطای 403/404 مناسب |

## جریان پیام‌ها: نکته معماری
در حال حاضر سه مفهوم باید در UI واضح و جدا بمانند:
1. `StudentMessage`: پیام دانش‌آموز به معلم و پاسخ آن.
2. `TeacherDirectMessage`: پیام مستقیم معلم به دانش‌آموز/والد.
3. `AcademyMessage`: اطلاعیه/پیام گروهی.

وجود فیلد `response` در جریان‌های فعلی به معنای «یک پیام به همراه یک پاسخ» است و به‌تنهایی یک گفت‌وگوی چندپیامی واقعی نیست. اگر هدف چت رفت‌وبرگشتی است، باید Conversation/Message با participantهای مشخص، چند پیام مرتب‌شده زمانی، read state و authorization در هر query طراحی شود. هر دو طرف باید Sent/Inbox و پاسخ را ببینند؛ شناسه‌های دستکاری‌شده، دانش‌آموز دیگر یا معلم نامرتبط نباید هیچ داده‌ای را افشا کنند.

## Design System واحد پیشنهادی
- یک منبع حقیقت برای tokenها: رنگ برند، سطوح، متن، border، radius، سایه، فاصله‌ها، اندازه‌های فونت و breakpointها.
- primitives مشترک: AppShell، Sidebar، Topbar، PageHeader/Breadcrumbs، KPI card، Panel/Card، Table، FilterBar، FormField، StatusBadge، Alert/Toast، EmptyState، Loading/Skeleton، Pagination و ConfirmDialog.
- هر دو نقش از همان scale، grid، typography فارسی، icon size، padding و رفتار responsive استفاده کنند؛ تفاوت فقط در منو، مجوز، داده و CTA نقش باشد.
- فایل‌های CSS نقش به entrypoint باریک محدود شوند؛ CSS مرجع داشبورد دانش‌آموز و overrideهای workspace باید مرحله‌ای به tokens/components منتقل شوند. حذف مستقیم CSS بدون screenshot/test ریسک regression دارد.
- RTL واقعی با logical properties مانند `margin-inline`, `padding-inline`, `inset-inline` و آزمون موبایل؛ از وارونه‌کردن دستی عناصر و LTR layoutهای ناخواسته پرهیز شود.
- همه صفحه‌ها باید stateهای loading، empty، error، success، disabled، pagination و validation را با کامپوننت‌های مشترک نشان دهند.

## چک‌لیست ممیزی لینک و داده برای هر صفحه
- لینک Sidebar/Topbar به route موجود و مجاز اشاره می‌کند؛ لینک به صفحه خالی یا route name ناموجود وجود ندارد.
- route با middleware و permission مناسب محافظت شده و Controller/Policy مالکیت رکورد را کنترل می‌کند.
- جدول/کارت‌ها از query واقعی استفاده می‌کنند؛ داده نمونه یا KPI ساختگی وارد production نمی‌شود.
- تمام دکمه‌ها رفتار مشخص دارند؛ فرم‌ها CSRF، validation، feedback، حالت خطا و redirect قابل پیش‌بینی دارند.
- پارامتر sort/filter/page اعتبارسنجی شده و با رفتن به جزئیات یا برگشت، فیلترهای لازم حفظ می‌شوند.
- شناسه رکورد با دستکاری URL باعث مشاهده یا ویرایش رکورد دیگران نمی‌شود.
- برای عملیات مخرب confirmation، برای عملیات موفق feedback و برای لیست خالی راهنمای اقدام نمایش داده می‌شود.
- screenshot QA در عرض دسکتاپ و موبایل، focus keyboard، contrast و متن‌های طولانی فارسی انجام می‌شود.

## آزمون‌های پذیرش لازم
- اجرای `php artisan route:list --path=teacher` و `php artisan route:list --path=student` و تطبیق با نام‌های Blade.
- اجرای Feature tests موجود: `StudentTeacherMessagingTest`, `StudentTeacherDashboardContractTest`, `StudentDashboardReferenceDesignTest`, `TeacherWorkspaceFlowTest`, `TeacherAccessIsolationTest`, `StudentPortalSmokeTest`, `StudentPortalUiContractTest`.
- افزودن/تکمیل آزمون‌ها برای لینک شکسته، permission denial، مالکیت رکورد، مسیرهای فرم ناموفق، پاسخ پیام در دو طرف، حذف تخصیص، empty states و mobile UI.
- اجرای `php artisan test` و `npm run build` در محیط پروژه و ثبت نتایج واقعی؛ تا اجرای این دستورات، نتیجه تست pass ادعا نشود.

## وضعیت تحویل
این PR تا این مرحله مستندات ممیزی و برنامه پیاده‌سازی را نگه می‌دارد. تغییرات تولیدی UI/route هنوز در این سند ادعا نشده‌اند. مرحله بعد باید با تغییرات کوچک، تست‌شده و قابل بازبینی در همان PR انجام شود؛ بدون merge مستقیم به `main`.
