# Sheykhan Database Schema Reference

## Purpose
This document is the reference for the finalized database/domain design before Teacher Portal work and before merging `feature/finalize-database-schema` into `main`.

## Domains
- Identity & Access: `users`, `roles`, `permissions`, `role_user`, `permission_role`
- Academy Core: `academies`, `academy_user`, profiles and parent/student relations
- Academic Reference: `academic_grades`, `academic_years`
- Learning: courses, grades, teachers, sections, lessons, classrooms, enrollments, live classes, progress, resources, assignments, exams, attendance
- Media: `media`, `media_attachments`
- Commerce: products, files, orders, items, payments, entitlements, downloads, protected files
- Legal: `legal_documents`, `legal_consents`
- Public Content: achievements, testimonials, academy content categories and contents
- Governance: student onboarding and audit logs
- Existing CMS: blog, SEO and settings

## Migration layout
The old monolithic `finalize_platform_schema` migration was removed. The branch now uses domain-focused migrations:

- `100100_create_academic_grades_and_years_tables`
- `100102_add_academic_account_fields`
- `100110_create_learning_schema`
- `100120_create_product_catalog_schema`
- `100121_create_commerce_transactions_schema`
- `100130_create_legal_schema`
- `100140_create_public_results_schema`
- `100142_create_academy_content_schema`
- `100150_create_onboarding_and_audit_schema`

## Important decisions
1. Course and Product are separate concepts.
2. Purchase is not access; `product_entitlements` controls access.
3. Course enrollment supports re-enrollment by academic year: `course_id + student_id + academic_year_id`.
4. Commerce money fields use integer minor units with a separate currency field.
5. Student national ID uses a deterministic lookup value plus encrypted storage; raw national ID should not be exposed.
6. Business-critical single-file relations use explicit `media_id`; multi-media content uses polymorphic media attachments.
7. Legal and audit history is preserved with conservative delete behavior.
8. Academy public content is academy-scoped.
9. Main seed data uses `firstOrCreate` and `syncWithoutDetaching` so re-running the seed does not overwrite manual CRUD changes.
10. Demo data is intentionally separate.

## Seeder map
| Seeder | Scope |
|---|---|
| RolePermissionSeeder | roles and permissions |
| AcademicReferenceSeeder | grades and academic year |
| AcademyFoundationSeeder | academy, users, profiles, memberships |
| LearningSeeder | learning domain and educational test records |
| CommerceSeeder | shop and purchase lifecycle |
| LegalSeeder | legal documents and sample consents |
| PublicContentSeeder | achievements, testimonials, 15 articles + 15 videos |
| BlogSeeder | blog data |
| SystemSeeder | SEO and settings |
| OnboardingSeeder | legacy student onboarding |
| AuditSeeder | audit sample |
| DemoContentSeeder | independent demo dataset |
| DemoDatabaseSeeder | demo-only orchestration |

## Required verification before merge
Run on the local test database:

```bash
php artisan optimize:clear
php artisan migrate
php artisan db:seed
```

For a disposable database:

```bash
php artisan migrate:fresh --seed
```

Demo only:

```bash
php artisan db:seed --class=DemoDatabaseSeeder
```

Before merge, also verify Owner CRUD for students, courses, classrooms, lessons, resources, products, orders, achievements and testimonials.


## LiveClass و ClassSchedule

این دامنه دو جدول مرتبط اما متفاوت دارد:

### live_classes
یک رویداد تاریخ‌دار است و شامل course_id، classroom_id اختیاری، teacher_id، title، description، provider، meeting_url، scheduled_at، duration_minutes و status است. Migration ضبط جلسه فیلدهای recording_media_id و recording_released_at، recording_visibility، scheduled_end_at، started_at و ended_at را تکمیل می‌کند.

### class_schedules
یک بازه هفتگی برای Classroom است: classroom_id، weekday، start_time، end_time، room، meeting_url و provider. Migration افزوده‌شده provider را برای رکوردهای قبلی با مقدار پیش‌فرض Jitsi تکمیل می‌کند؛ لینک‌های قدیمی دست‌نخورده می‌مانند.

### روابط و قاعده دسترسی
- Course ظرف محتوا و درس است.
- Classroom گروه دانش‌آموزان و مدرس‌های مرتبط با Course است.
- ClassSchedule اتاق/زمان تکرارشونده را مشخص می‌کند و برای همهٔ هفته‌ها همان URL را به کار می‌برد.
- LiveClass یک زمان دقیق و مستقل دارد و می‌تواند به Classroom خاص محدود شود یا در scope Course باشد.
- StudentAccessService کنترل می‌کند Enrollment و پرداخت Course، عضویت Classroom، زمان مجاز و provider/url با هم سازگار باشند.
- فایل ضبط‌شده در Media خصوصی نگهداری می‌شود و زمان انتشار آن تعیین می‌کند چه زمانی قابل مشاهده باشد.

Provider:
- Jitsi: URL اتاق یکتا از سمت شیخان ساخته می‌شود؛ اتاق در Jitsi با نام آن استفاده می‌شود.
- Google Meet و Zoom: تنها URL واقعی ساخته‌شده در سرویس بیرونی ذخیره می‌شود تا یک URL حدسی اشتباهاً به‌عنوان جلسه واقعی ثبت نشود.

Migration افزوده‌شده برای provider را به شکل backward-compatible اجرا کنید. برای rollout ابتدا نسخه‌ای مستقر شود که migration جدید را اجرا کند، سپس کد جدید را فعال کنید. در Deploy باید هم‌زمان migration و build/route cache طبق رویه Production پاک/بازسازی شود.
