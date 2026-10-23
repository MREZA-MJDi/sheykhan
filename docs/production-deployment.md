# Sheykhan production deployment runbook

Production deployment is intentionally manual. Do not deploy until the release PR is merged, the SQLite and MariaDB CI jobs pass, and the server has a verified database/storage backup.

## GitHub configuration
Create a protected GitHub Environment named `production` and configure these secrets:
- `DEPLOY_HOST`: server hostname/IP
- `DEPLOY_USER`: SSH deployment user (not root)
- `DEPLOY_PATH`: application working tree on the server
- `DEPLOY_SSH_KEY`: private SSH key for that user
- `DEPLOY_KNOWN_HOSTS`: pinned SSH host key line(s), collected and verified out of band

The server needs Git, PHP with the extensions listed by Laravel/composer, Composer, Node/npm, tar/gzip, MySQL/MariaDB client tools, and (when paid PDFs are enabled) Ghostscript. For session videos up to 200 MB, configure PHP `upload_max_filesize` and `post_max_size` to at least 220M and Nginx `client_max_body_size` to at least 220M, then reload PHP-FPM/Nginx after reviewing server capacity. Configure the correct production `.env`, private file storage, TLS, queue/scheduler, rate limits, database grants, and log rotation before first release.

## Deployment behavior
The workflow runs only from `main`, requires an explicit `deploy` confirmation, verifies the SSH host using the pinned key, creates a private storage/media archive and a database dump before migrations, updates the existing checkout with fast-forward-only Git, builds assets, runs migrations, clears/rebuilds Laravel caches, restarts queue workers and exits maintenance mode. On any failure after maintenance mode begins, the site deliberately stays in maintenance mode; an operator must inspect logs and backups before restoring traffic. The workflow does not perform an automatic rollback of the schema or files.

The pre-deploy snapshots live on the same server disk and are rollback aids, not offsite disaster-recovery backups. Configure a separate encrypted offsite backup schedule and test restoration. The workflow does not configure the server, create database users, enable HTTPS, provision the gateway, or upload real academy content. Those are one-time infrastructure/business setup tasks. A passing deployment does not replace post-deploy smoke tests.

## Private staging: quick-login test accounts

Use this only on a private staging host, never as a public production convenience. Set the staging environment and a unique password in the server's private .env:

    APP_ENV=staging
    APP_DEBUG=false
    SHOW_TEST_ACCOUNTS=true
    SEED_USER_PASSWORD=replace-with-a-unique-long-staging-secret

Then run the role and demo identity seeders intentionally:

    php artisan db:seed --class="Database\Seeders\RolePermissionSeeder"
    php artisan db:seed --class="Database\Seeders\AcademyFoundationSeeder"
    php artisan config:clear
    php artisan config:cache

The login screen includes the demo password only when test-login access is explicitly enabled and a seed password is configured. Local/testing may use the documented disposable default; staging must supply its own password. The SEO test identity is seo@sheykhan.test; it receives the restricted seo-specialist role, not academy-owner privileges. These seeders create synthetic demo identities and should not be run against a live customer database.

If the host must keep APP_ENV=production, set SHOW_TEST_ACCOUNTS=true, configure SEED_USER_PASSWORD, and set TEST_ACCOUNTS_ALLOWED_IPS to a comma-separated list of trusted public client IPs. The panel remains hidden to other IPs. Remove the allow-list and disable test accounts once testing is finished.

## Before first production use
1. Snapshot/back up the DB and private uploaded files. Verify that the backup can be restored.
2. Review migrations with production data assumptions in mind.
3. Set `APP_ENV=production`, `APP_DEBUG=false`, a strong `APP_KEY`, database/session/cache settings and trusted proxy/HTTPS configuration.
4. Set an explicit `SEED_USER_PASSWORD` only if demo seeders are intentionally run. Do not run demo seeders on a live database unless approved.
5. Configure a real payment gateway and verify signed callbacks/idempotency before making checkout available. Do not mark pending orders paid manually without independent payment evidence.
6. Publish lawyer-approved purchase/copyright terms and record valid media releases before publishing identifiable student records or testimonials.
7. Upload approved, real materials. Placeholder seed content is not a substitute for academy-provided assets.
8. Verify recordings, resource access, purchased downloads, watermark output, mail delivery, scheduled jobs, backup alerts and error monitoring.

## Post-deploy smoke tests for online classes

After a release that includes the online-class provider or weekly-schedule migration:

1. Verify the provider migration completed with php artisan migrate:status and confirm class_schedules has the provider column.
2. As a teacher, select Jitsi on the weekly schedule form and verify a unique meet.jit.si URL appears before saving.
3. Create a dated Jitsi LiveClass and verify that the URL and scheduled_end_at are persisted.
4. If testing Google Meet or Zoom, create a real participant link in that provider first, then submit it in Sheykhan. A provider selection without a real URL is expected to show validation feedback; no Google/Zoom API integration is configured by this release.
5. As an enrolled student with active classroom membership, verify the fixed weekly timetable and dated LiveClass are shown as separate sections; verify the Join action is unavailable outside the allowed time window.
6. Check that a student outside the class, without eligible course access, or before the release time cannot open the room or private recording.
7. Inspect Laravel logs and reverse-proxy logs if any page returns HTTP 500. Do not expose APP_DEBUG on the public site.

Persian operational references:
- docs/FEATURE_CATALOG_FA.md
- docs/ONLINE_CLASSES_AND_SCHEDULES_FA.md
- docs/ROUTES_AND_PERMISSIONS_FA.md

## Rollback
Do not blindly roll back a database migration if users have created records on the new schema. Restore the verified backup or run a reviewed forward-fix migration. Keep the previous release/build available and record the deployed commit SHA.
