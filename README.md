# TAYA

TAYA is a Laravel 13 detainee rights and overstay alert application. It uses PostgreSQL, server-rendered Blade/Vite assets, admin-provisioned accounts, role-based access control, password reset, encrypted sessions, private document downloads, audit logs, validation, throttling, and production security headers.

See [SECURITY.md](SECURITY.md) for the operational checklist and remaining infrastructure requirements.

## Roles

- `admin`: all application and administration capabilities.
- `staff`: operational case access, restricted to the assigned facility when one is set.
- `lawyer`: case review, alert assignment/resolution, legal actions, and reports.
- `auditor`: read-only case/report access plus audit logs.
- `authorized_user`: backward-compatible legacy access; migrate these accounts to a specific role.

Public registration is disabled by default. Staff sign in directly with their provisioned email address and password.

## Local setup with PostgreSQL

Requirements: PHP 8.4.1+, Composer, Node.js 22+, npm, and PostgreSQL client support (`pdo_pgsql`).

```bash
composer install
cp .env.example .env
php artisan key:generate
npm ci
npm run build
php artisan migrate
php artisan serve
```

Set `APP_ENV=local`, `APP_DEBUG=true`, and a local PostgreSQL `DB_URL` in your untracked `.env`. To create the first admin safely, set `ADMIN_EMAIL` and a strong `ADMIN_PASSWORD`, then run:

```bash
php artisan db:seed --class=UserSeeder
```

The seeder is idempotent and never resets an existing admin password. Do not run the full demo `DatabaseSeeder` in production.

## Supabase

Create a Supabase project and copy its PostgreSQL direct or session-pooler connection string into `DB_URL`. For an IPv4 Render service, Supabase's session-mode pooler on port 5432 is the safest default. Keep `DB_CONNECTION=pgsql`, `DB_EMULATE_PREPARES=false`, and at least `DB_SSLMODE=require`; for full certificate identity verification, mount Supabase's CA certificate, set `DB_SSLROOTCERT`, and use `DB_SSLMODE=verify-full`. The included migrations are portable across PostgreSQL and the SQLite test database.

Supabase database passwords must be URL-encoded inside `DB_URL`. Never commit `.env`, dashboard service keys, database dumps, or connection strings. Rotate any credential that was previously committed.

### Private document storage

1. In Supabase, open **Storage**, create a bucket named `taya-documents`, and keep **Public bucket disabled**.
2. Open **Storage > S3 Configuration**, enable the S3 protocol, and generate a server-side access-key pair.
3. Copy the endpoint, region, access-key ID, and secret into the matching `SUPABASE_STORAGE_*` Render secrets. Never put these credentials in frontend/Vite variables.
4. Keep `DOCUMENTS_DISK=supabase` in production. Authorized downloads are streamed through Laravel; the private bucket is never exposed directly to the browser.

If this installation already has local documents, configure the Supabase variables locally and run `php artisan documents:migrate-to-supabase`. Verify the remote files first, then optionally rerun with `--delete-local`. The command changes a database record to the Supabase disk only after verifying its remote object.

## Render deployment

This repository includes [render.yaml](render.yaml) and a multi-stage [Dockerfile](Dockerfile). The current Blade architecture deploys as one Render web service: Vite compiles the frontend into the image and Laravel serves both the UI and backend. A separate static frontend service would require converting the UI into an independent SPA/API client.

1. Push the repository to a private Git host and create a Render Blueprint from `render.yaml`.
2. Set `APP_URL` to the final HTTPS Render URL.
3. Generate `APP_KEY` locally with `php artisan key:generate --show` and store it as a Render secret.
4. Store the Supabase connection string in the Render `DB_URL` secret.
5. Create the private Supabase Storage bucket and configure all `SUPABASE_STORAGE_*` secrets described above.
6. Configure all SMTP variables; password reset depends on working email. Render Free blocks outbound SMTP ports 25, 465, and 587, so select a provider endpoint on an allowed port such as 2525.
7. Set one-time bootstrap secrets `ADMIN_EMAIL` and a strong `ADMIN_PASSWORD`. After the first successful deploy, remove `ADMIN_PASSWORD`; the seeder will never change the existing account.
8. Deploy. The container caches Laravel configuration/views/routes, runs migrations, and idempotently creates the first admin.

### Optional demonstration data

Set `SEED_SAMPLE_DATA=true` to load the bundled demonstration facilities, penalty references, users, detainees, phases, alerts, and legal actions. The sample loader records `taya-sample-data-v1` in the `data_seed_runs` table and safely skips later deploys, so the records are created only once. Demonstration users receive random unknown passwords; use User Management to assign a password if a specific sample account must be used for testing. Keep this setting disabled for installations that will contain real records.

Render Free sleeps after inactivity and is explicitly intended for previews/hobby workloads rather than sensitive production systems. Uploaded documents now use durable private Supabase Storage, but scheduled checks still run only while the web instance is awake.

## Verification

```bash
composer test
npm run build
php artisan route:list
```

Production settings should keep `APP_DEBUG=false`, `REGISTRATION_ENABLED=false`, encrypted secure cookies, HTTPS, and a real SMTP provider.
