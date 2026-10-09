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

## Before first production use
1. Snapshot/back up the DB and private uploaded files. Verify that the backup can be restored.
2. Review migrations with production data assumptions in mind.
3. Set `APP_ENV=production`, `APP_DEBUG=false`, a strong `APP_KEY`, database/session/cache settings and trusted proxy/HTTPS configuration.
4. Set an explicit `SEED_USER_PASSWORD` only if demo seeders are intentionally run. Do not run demo seeders on a live database unless approved.
5. Configure a real payment gateway and verify signed callbacks/idempotency before making checkout available. Do not mark pending orders paid manually without independent payment evidence.
6. Publish lawyer-approved purchase/copyright terms and record valid media releases before publishing identifiable student records or testimonials.
7. Upload approved, real materials. Placeholder seed content is not a substitute for academy-provided assets.
8. Verify recordings, resource access, purchased downloads, watermark output, mail delivery, scheduled jobs, backup alerts and error monitoring.

## Rollback
Do not blindly roll back a database migration if users have created records on the new schema. Restore the verified backup or run a reviewed forward-fix migration. Keep the previous release/build available and record the deployed commit SHA.
