# Sheykhan — VPS test deployment on port 8082

This deployment is intentionally isolated from the existing Jani site on port 8081.

## Runtime

- App: /var/www/sheykhan-test
- Public root: /var/www/sheykhan-test/public
- Nginx: 45.149.79.155:8082
- PHP-FPM: /run/php/php8.5-fpm.sock
- Database: MySQL, database `sheykhan`, user `sheykhan`

## Server .env

Create `.env` from `.env.example` and set:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://45.149.79.155:8082

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sheykhan
DB_USERNAME=sheykhan
DB_PASSWORD=<server-secret>

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

SEED_USER_PASSWORD=<seed-account-password>
```

Never commit the real password to Git. This server config uses `APP_ENV=production`: do not run `php artisan db:seed --force` against the live database because demo seeders can create or reset demo users. Provision required users through the reviewed admin/onboarding workflow.

## Deployment

```bash
cd /var/www/sheykhan-test
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan storage:link
php artisan optimize:clear
mkdir -p storage/app/private storage/app/public storage/app/deploy-backups
php deploy/backup-database.php
tar -czf "storage/app/deploy-backups/storage-$(date +%Y%m%d-%H%M%S).tar.gz" --exclude='private/deploy-backups' -C storage/app private public
php artisan migrate --force
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache
```

## Nginx

```bash
install -m 0644 deploy/nginx/sheykhan-8082.conf /etc/nginx/sites-available/sheykhan
ln -sfn /etc/nginx/sites-available/sheykhan /etc/nginx/sites-enabled/sheykhan
nginx -t
systemctl reload nginx
```

## Verification

```bash
curl -I http://127.0.0.1:8082
curl -I http://45.149.79.155:8082
php artisan migrate:status
```

The seeded home banners use public media attached to the academy and are editable from the academy home-banner management screen. Student and teacher dashboards consume relational course/classroom/lesson/assignment/exam/attendance/live-class/progress records rather than hard-coded UI values.
