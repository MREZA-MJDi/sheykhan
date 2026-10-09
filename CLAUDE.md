# Sheykhan — Repository Instructions

## Product intent
Sheykhan is a Persian-first, RTL academy platform built on Laravel 12, PHP 8.2+, Blade, Tailwind/Vite and Alpine. Core flows include academy owners, teachers, students, parents, course enrollment, recorded sessions, learning resources, public academy content, achievements, testimonials and a digital-products shop.

## Non-negotiable rules
- Keep the interface Persian-first and RTL; preserve the existing design system and layouts.
- Never mix data between academies. Every owner write must be scoped to an academy owned/manageable by the authenticated user.
- Never trust hidden form inputs for role, academy, product price, order total, entitlement status or payment state. Recalculate prices on the server.
- Only verified successful payments may mark an order paid or grant product entitlements.
- Persist the exact legal-document version and content hash accepted by the buyer. No current active terms/copyright documents means checkout must be blocked.
- Protect recordings, lesson files and purchased product files on private storage. Do not expose a source PDF when a watermarked derivative is required.
- A seed user/password or seeded payment is demo data only. Do not introduce predictable production credentials or fake successful purchases.
- For identifiable student achievements or testimonial media, require recorded publication consent before public release. For minors, the academy must confirm a valid guardian release.
- Keep upload/delete operations failure-safe: if a DB transaction rolls back, clean up newly written files.
- Never run migrations, seeders, or production deployment against a real server without a reviewed backup and an explicit manual deployment action.
- Do not mark work complete until relevant tests pass. Existing CI checks both SQLite and MariaDB; add coverage for changes to authorization, migrations, commerce, upload cleanup and media exposure.

## Standard workflow
1. Read the relevant model, migration, service, controller, policy and feature tests before changing a flow.
2. Create a feature branch from `main`. Do not force-push or rewrite shared branch history.
3. Make the smallest cohesive change and include a regression test for each security-sensitive behavior.
4. Run:
   - `composer install`
   - `npm ci`
   - `npm run build`
   - `php artisan test --compact`
   - `php artisan migrate:fresh --env=testing` only against an isolated test DB, never a production DB.
5. Verify MariaDB behavior in CI; SQLite passing does not establish MySQL/MariaDB compatibility.
6. Inspect the final diff for authorization leaks, missing route names, cache invalidation, accidental public storage, N+1 queries and fake seed content.
7. Deploy only from `main` through the manually approved production workflow after CI succeeds and a database backup is verified.

## Build and release boundaries
- Payment gateway credentials are secrets and must come from environment/config. Do not add a fake/local gateway as a production substitute.
- PDF watermarking must fail closed if the watermark engine is unavailable; never fall back to sending the unmarked source.
- The site must not claim that real academy videos, consent records or legal content exist when only seed placeholders are present.
- Claude Cowork is a developer work surface, not a Laravel runtime dependency. Use this file as repository context; do not add a server-side Cowork dependency or claim an in-app integration without an actual supported API.
