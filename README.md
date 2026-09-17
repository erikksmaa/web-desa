# Website Informasi Desa

A public Indonesian village information website with a lightweight administrator CMS.

Phase 0 established the architecture in [PROJECT_CONTEXT.md](PROJECT_CONTEXT.md). Phase 1 initializes the foundation only: public placeholder, admin session authentication, dashboard and Bootstrap layouts. The original static prototype is still unavailable. No business CMS modules are implemented.

## Requirements

- PHP 8.3+ with Laravel-required extensions, pdo_mysql, and pdo_sqlite for isolated tests.
- Composer 2.x. Dependencies resolve against PHP 8.3 through config.platform.php.
- Node 24.x and npm.
- MySQL 8.0+; MySQL 8.4 is not required.

Resolved foundation: Laravel 13.32.0, Bootstrap 5.3.8, Vite 7.3.6, Laravel Vite plugin 2.1.0. The lockfiles record exact dependencies.

## Local setup

Run commands from the project root:

```powershell
composer install
Copy-Item .env.example .env
```

Only copy the example on first setup; do not overwrite an existing .env. Set your actual local MySQL credentials only in .env. Defaults are DB_CONNECTION=mysql, DB_HOST=127.0.0.1, DB_PORT=3306 and DB_DATABASE=web_desa. Create an empty web_desa database with utf8mb4 using your local database tool if needed. Verify the target database before migrating.

The application uses APP_NAME="Website Desa", APP_TIMEZONE=Asia/Jakarta, APP_LOCALE=id and APP_FALLBACK_LOCALE=en. APP_DEBUG defaults to false. Set APP_URL to your local application's address.

```powershell
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Open http://127.0.0.1:8000 and http://127.0.0.1:8000/admin/login. For frontend development, run npm run dev in a separate terminal while the PHP server is running.

Sessions use MySQL, cache uses files, and queues run synchronously. No queue worker is needed. Upload handling is deferred; public/storage has not been linked. Local automatic temporary-file routes are disabled until required by an authorized storage phase.

## Development administrator

With APP_ENV=local, AdminUserSeeder creates the demonstration account admin@example.com with password password, hashed using Laravel. These are public development-only credentials, not real credentials. The seeder refuses non-local environments and never overwrites an existing account. Do not use the demonstration account in production; replace development credentials before production.

There is no registration, password reset, email verification or public account system.

## Verification

```powershell
composer validate
composer check-platform-reqs
php artisan route:list
php artisan migrate:status
php vendor/bin/pint --test
php artisan test
npm run build
git status --short
```

Feature tests force SQLite :memory: and refuse to run if the resolved configuration targets another database, including cached MySQL configuration. They never reset the development MySQL database. Clear stale configuration with php artisan config:clear if that guard stops a test run. Views in feature tests omit Vite; verify actual assets separately with npm run build and HTTP checks.

Phase 1 verification: 13 feature tests, baseline MySQL migrations, asset build and real HTTP login/logout/CSRF checks passed. Automated visual browser checks could not run because the browser runtime failed to start; mobile/tablet/desktop visual inspection remains pending.

Git was initialized without a commit. A path-specific safe.directory entry was needed locally because the sandbox created Git metadata under a different Windows account. This is a local Git configuration entry, not a project dependency.

Phase 2 adds the approved business schema, models, factories and local sample data without CRUD or public content pages. Run php artisan migrate followed by php artisan db:seed locally; seeders preserve existing values and administrator credentials. Documents/albums use draft placeholder file references, and demo banners are inactive. No physical sample files are generated. The complete SQLite suite now passes 67 tests (316 assertions). See PROJECT_CONTEXT.md for finalized relationships and deletion policies.

Phase 3 adds the reusable admin shell, dashboard summaries, Blade components, Bootstrap pagination, centralized confirmation/file-preview JavaScript, and a separate admin CSS bundle. No routes, dependencies, schema, uploads, or CRUD modules were added.

Wait for external review before Phase 4. Do not run destructive database reset commands.
