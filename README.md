# Sheykhan — Academy & Learning Platform

Sheykhan is a Laravel application for an education/academy workflow. Its routes and controllers cover public academy/course/teacher content, owner-side academy and classroom management, enrollments and reports, student learning features, and a parent portal. The exact availability of each workflow depends on the current implementation; consult routes, tests, and migrations before describing a feature as production-ready.

## Dedicated dashboards
Sheykhan provides dedicated role-based dashboards for its academy workflows, including the relevant owner, student, and parent experiences. Check the current route middleware and tests when verifying which operations each role may access.

## Stack
- PHP `^8.2`, Laravel `^12.0`
- Blade, Vite and the frontend dependencies in `package.json`
- Relational database through Laravel Eloquent/migrations
- PHPUnit tests

## Requirements
PHP 8.2+, Composer, Node.js/npm, and a supported database such as MySQL or MariaDB.

## Local setup
```bash
git clone https://github.com/MREZA-MJDi/sheykhan.git
cd sheykhan
composer install
```

Copy `.env.example` to `.env` (`copy .env.example .env` on Windows CMD; `cp .env.example .env` on macOS/Linux). Create a local database and configure `DB_*` values before migrating.

```bash
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan storage:link
php artisan serve
```

Open `http://127.0.0.1:8000`. During frontend development, run `npm run dev` in a second terminal.

## Tests
```bash
php artisan test
```

## Files, media and production
Review upload validation, storage disk settings, access policies, and role middleware before deploying student/course media. Keep private recordings and student information behind authorization checks; never assume that an unlisted URL is sufficient access control. Back up the database and uploaded files before releases.

## Product and developer documentation

The Persian documentation index is [docs/README-fa.md](docs/README-fa.md).

- [Feature catalog and role workflows](docs/FEATURE_CATALOG_FA.md)
- [Dashboard route map, teacher access audit and legacy-student import](docs/PLATFORM_DASHBOARD_AND_LEGACY_IMPORT_FA.md)
- [Private WordPress student import runbook](docs/LEGACY_STUDENT_IMPORT_FA.md)
- [Online classes, weekly schedules, providers and student join rules](docs/ONLINE_CLASSES_AND_SCHEDULES_FA.md)
- [Routes and permissions reference](docs/ROUTES_AND_PERMISSIONS_FA.md)
- [Platform role workflow](docs/platform-workflow.md)
- [Database schema reference](docs/database-schema.md)
- [Production deployment](docs/production-deployment.md)

The online meeting workflow distinguishes a course, a classroom/group, a recurring weekly schedule and a date-specific live session. Jitsi room URLs can be generated without provider API credentials; Google Meet and Zoom require a real URL created in the provider until their official API/OAuth integrations are configured.

## Links
- Repository: https://github.com/MREZA-MJDi/sheykhan
- Laravel documentation: https://laravel.com/docs/12.x
