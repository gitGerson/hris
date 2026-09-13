# HRIS — Rumah Roti

An HR information system built on Laravel 13 and Filament 5. It covers the company's
master data — company profile, branches, departments, positions, job levels and
employees — with role-based access control, an audit trail, and file storage on
Cloudflare R2.

---

## Stack

| Layer | Choice |
|---|---|
| Framework | Laravel 13 (PHP 8.4) |
| Admin panel | Filament 5 |
| Database | PostgreSQL |
| Cache & queue | Redis (via `predis/predis`) |
| Sessions | PostgreSQL — see note below |
| File storage | Cloudflare R2, served from a custom domain |
| Frontend build | Vite 8 + Tailwind 4 |
| Tests | Pest 5 |

### Filament plugins

| Package | Purpose |
|---|---|
| `bezhansalleh/filament-shield` | Roles and permissions on top of `spatie/laravel-permission` |
| `jeffgreco13/filament-breezy` | My Profile page, avatar upload, browser sessions |
| `pxlrbt/filament-activity-log` | Activity log viewer, backed by `spatie/laravel-activitylog` |
| `diogogpinto/filament-auth-ui-enhancer` | Split-screen login with rotating background |
| `fahiem/filament-pinpoint` | Leaflet map picker for branch geofences |

---

## Requirements

- PHP **8.4** with the usual Laravel extensions and `pdo_pgsql`
- PostgreSQL on port **5432**
- Redis (cache and queue)
- Node **22+** and npm
- Composer 2

Redis needs a PHP client. This project uses **predis** (pure PHP), so the
`phpredis` extension is *not* required.

---

## Setup

```bash
git clone <repo> hris
cd hris

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Create your PostgreSQL database first, then edit `.env` with its credentials (the defaults are `DB_CONNECTION=pgsql`, `DB_HOST=127.0.0.1`, `DB_PORT=5432`, and `DB_USERNAME=postgres`). Set `DB_DATABASE` and `DB_PASSWORD` for your local server, then:

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
```

Create the first admin user and grant it everything:

```bash
php artisan make:filament-user
php artisan shield:generate --all --panel=admin
php artisan shield:super-admin --user=1 --panel=admin
```

Then run the app:

```bash
composer dev     # serve + queue listener + vite, all at once
```

The panel lives at `/admin`.

### Environment notes

**Redis** — cache and queue only:

```env
REDIS_CLIENT=predis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=database
```

`SESSION_DRIVER` stays on `database` deliberately: Breezy's *Browser Sessions*
feature reads `config('session.table')` and only works with the database driver.

**Cloudflare R2** — the default filesystem:

```env
FILESYSTEM_DISK=r2
LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=local

R2_ACCESS_KEY_ID=
R2_SECRET_ACCESS_KEY=
R2_DEFAULT_REGION=auto
R2_BUCKET=
R2_ENDPOINT=https://<account-id>.r2.cloudflarestorage.com
R2_URL=https://cdn.example.com
R2_USE_PATH_STYLE_ENDPOINT=false
```

- `R2_ENDPOINT` is the account host **without** the bucket; the bucket goes in `R2_BUCKET`.
- `R2_URL` must be the public bucket URL or custom domain. The S3 API endpoint is not
  publicly readable, so without it every `Storage::url()` link fails.
- `FILESYSTEM_DISK` drives both `filesystems.default` and Filament's
  `config('filament.default_filesystem_disk')`, so one variable moves the whole app.
  Set it to `local` to develop without R2.
- The `s3` disk is kept separate for real AWS and is not used by default.
- Livewire stages uploads on the private `local` disk, then Laravel saves the final
  files to R2. This avoids browser-to-R2 CORS preflight requests for temporary uploads.

Saved file previews still fetch from R2 in the browser. In Cloudflare, open
**R2 → your bucket → Settings → CORS policy** and add this rule to the existing
policy (preserve rules used by other applications):

```json
[
  {
    "AllowedOrigins": ["http://hris.test"],
    "AllowedMethods": ["GET", "HEAD"],
    "AllowedHeaders": ["*"],
    "ExposeHeaders": ["ETag"],
    "MaxAgeSeconds": 3600
  }
]
```

Add the exact production origin when deploying. CORS does not make private objects
public; Filament's signed preview URLs still authorize access. After saving, reload
the profile page with browser caching disabled and confirm the avatar response has
`Access-Control-Allow-Origin: http://hris.test`.

See [Cloudflare's R2 CORS documentation](https://developers.cloudflare.com/r2/buckets/cors/).

**Map picker** — `PINPOINT_PROVIDER=leaflet` uses OpenStreetMap and needs no API key.

---

## Data model

```
Company (single row, edited as a settings page)
 └── Branch          code, geofence (lat/lng/radius), timezone
 └── Department      code, name
      └── Position   code, name
JobLevel             code, name, rank
Employee             identity, dual address, placement, employment dates
 ├── branch_id · department_id · position_id · job_level_id
 └── identity/domicile → Province · City
```

Notes on the shape:

- **Single company.** The company is one row edited through *Settings → Company*, not a
  CRUD resource. `branches.company_id` and `departments.company_id` still exist and are
  auto-filled from `Company::current()`, so a second entity later is a seeder row rather
  than a migration.
- **No repeated foreign keys.** Positions reach the company through their department;
  employees reach it through their branch. The key is stored once.
- **Employee numbers** are generated as `RR0001`. The generator is a static method, so
  seeders can call it directly — model events are suppressed under `WithoutModelEvents`.
- **Regions** are local tables (38 provinces, 514 cities) seeded from
  [wilayah.id](https://wilayah.id) and cached to `database/data/regions.json`, so seeding
  works offline after the first run.
- **Soft deletes** on all master data; history is never hard-deleted.

### Seeded data

`php artisan migrate --seed` gives you: the Rumah Roti company and its head office
branch, 6 departments, 14 positions, a 6-rung job level ladder, 8 employees, and the
full Indonesian region tables. Every seeder is idempotent — re-running updates rather
than duplicating.

---

## Conventions

Project rules live in [`.ai/rules/`](.ai/rules/) and are indexed by file glob. Read the
matching rule file before editing:

| Applies to | Rule |
|---|---|
| `app/Filament/**` | File uploads target R2; never call `->visibility()` |
| `app/Filament/Resources/**/Tables/**` | Table pattern: session persistence, icon-only row actions |

Two that bite if ignored:

- **Never chain `->visibility('public')` on a `FileUpload`.** R2 has no S3 ACLs, so the
  call fails — and Filament wraps it in `rescue(..., report: false)`, so it fails
  *silently* and looks fine.
- **State toggles belong in the table, not the form**, as an action with
  `->requiresConfirmation()`. `ToggleColumn` cannot confirm; `requiresConfirmation()`
  only exists on actions.

### Audit trail

Master data models log changes via `spatie/laravel-activitylog`. `Employee` deliberately
excludes `national_id`, `phone` and both addresses from the log — identity data should
not end up in an audit table.

---

## Commands

```bash
composer dev                  # serve + queue + vite
php artisan test --compact    # run the suite
vendor/bin/pint --dirty       # format changed files

php artisan migrate:fresh --seed          # rebuild the database
php artisan shield:generate --all --panel=admin   # regenerate permissions after adding a resource
```

After adding a Filament resource, run `shield:generate` or the new resource will have no
permissions and will be invisible to non-super-admins.

---

## Roadmap

Master data is complete. The modules it was built to support are not yet started:

- **Attendance** — branches already carry timezone, coordinates and a geofence radius;
  employees carry an optional fingerprint ID
- **Payroll** — job levels are ranked for salary banding; `marital_status` is stored for
  PTKP
- **Leave** — `join_date` is in place for entitlement accrual
