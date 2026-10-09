# Sheykhan — Academy & Learning Platform

Sheykhan is a Laravel application for an education/academy workflow. Its routes and controllers cover public academy/course/teacher content, owner-side academy and classroom management, enrollments and reports, student learning features, and a parent portal. The exact availability of each workflow depends on the current implementation; consult routes, tests, and migrations before describing a feature as production-ready.

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

## Links
- Repository: https://github.com/MREZA-MJDi/sheykhan
- Laravel documentation: https://laravel.com/docs/12.x
