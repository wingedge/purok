# Live Site Deployment Guide

Use this checklist when moving the Purok application to the live server.

## Before Deployment

- Confirm the live server runs PHP 8.2 or newer. PHP 8.3 is preferred.
- Confirm the live database is backed up.
- Confirm `.env` values for the live site:
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - `APP_URL=https://your-live-domain`
  - database credentials
  - mail credentials for password reset
- Confirm file permissions allow Laravel to write to:
  - `storage/`
  - `bootstrap/cache/`

## Upload Or Pull Code

- Put the application code on the live server.
- Do not upload local `.env`, `storage/logs`, or development database files.
- Point the web server document root to the Laravel `public/` directory.

## Install Dependencies

Run on the live server:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

If the server does not build frontend assets, build locally and upload the generated public build assets with the release.

## Laravel Setup

Run on the live server:

```bash
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Only run `key:generate` for a new live installation. Do not regenerate the app key on an existing live site unless you understand the impact on encrypted data and sessions.

## Back-Office URLs

The Filament backend is now the primary back office:

- `/admin`
- `/admin/members`
- `/admin/expenses`
- `/admin/incomes`
- `/admin/inventories`
- `/admin/rentals`
- `/admin/purok-certificates`
- `/admin/contributions`
- `/admin/contribution-grid`
- `/admin/data-exchange`
- `/admin/reports`

Old Blade back-office entry pages redirect to Filament. Member portal and auth profile routes remain Blade-based:

- `/member/profile`
- `/profile`

## First Login Checks

- Login as an admin user.
- Visit `/admin`.
- Confirm staff users can access members, rentals, certificates, and contribution reports.
- Confirm treasurer users can access expenses, incomes, contributions, cash-flow reports, and import/export.
- Confirm member users are redirected to `/member/profile` and cannot access `/admin`.

## Smoke Tests

After deployment, check:

- Create/edit a member and dependent in Filament.
- Create/update an expense and income.
- Record and remove a contribution from the contribution grid.
- Create and return a rental.
- Print cash-flow and contribution reports.
- Import and export one small CSV file on a staging copy before doing it on live data.
- Update a member portal profile from a member account.

## Rollback Plan

- Keep the previous release available until the smoke tests pass.
- Keep the pre-deployment database backup.
- If deployment fails before migrations, switch the web root or release symlink back to the previous release.
- If deployment fails after migrations, restore the database backup before switching code back.

## Ongoing Maintenance

- Run `php artisan optimize:clear` after changing `.env`.
- Re-run cache commands after each deployment.
- Watch `storage/logs/laravel.log` after the first live login and first import/export.
