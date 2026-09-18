# 01 — Foundation

Goal: every later commit is checked by strict tooling. No domain code in this phase.

Starting point: a fresh `laravel new golden-court` with the Vue starter kit, Pest, and Laravel's built-in auth, connected to a local PostgreSQL 17 database named `golden_court`.

## Tasks

### 1.1 Database configuration
- Set `.env` and `.env.example` to `DB_CONNECTION=pgsql`, host `127.0.0.1`, port `5432`, database `golden_court`.
- In `phpunit.xml`, remove the SQLite in-memory overrides and point tests at a database named `golden_court_test` on the same server. Document in the README how to create it (`createdb golden_court_test`).
- Use `RefreshDatabase` in Pest's `tests/Pest.php` for Feature tests.
- Confirm `php artisan migrate` and `php artisan test` both succeed against Postgres.

Commit: `Configure PostgreSQL for app and tests`

### 1.2 PHP strictness
- Install `larastan/larastan` as a dev dependency. Create `phpstan.neon` at level 8 scanning `app/`, `database/`, `routes/`, `tests/`.
- Configure Pint (`pint.json`) with the `laravel` preset plus `declare_strict_types: true`, `strict_comparison: true`, `strict_param: true`, `final_class: false`. Run `vendor/bin/pint` and commit the reformatted files separately.
- Add `declare(strict_types=1);` to every existing PHP file (Pint will do this once the rule is on).
- In `AppServiceProvider::boot()` add:
  - `Model::shouldBeStrict();`
  - `Model::unguard()` is **not** allowed; use `$fillable`/`$guarded` explicitly.
  - `DB::prohibitDestructiveCommands($this->app->isProduction());`
  - `Date::use(CarbonImmutable::class);`
- Fix everything PHPStan reports until level 8 is clean.

Commit: `Enable strict types, Pint and Larastan level 8`

### 1.3 TypeScript strictness
- In `tsconfig.json` set `"strict": true`, `"noUncheckedIndexedAccess": true`, `"noImplicitOverride": true`.
- Ensure `vue-tsc` is a dev dependency and add script `"type-check": "vue-tsc --noEmit"`.
- Ensure ESLint runs with the Vue + TypeScript config the starter kit ships. Add `"lint": "eslint . --max-warnings=0"`.
- Fix any errors in the starter kit code that the stricter settings surface.

Commit: `Enable strict TypeScript and lint scripts`

### 1.4 Quality scripts
Add to `composer.json`:
```json
"scripts": {
  "lint": "vendor/bin/pint --test",
  "analyse": "vendor/bin/phpstan analyse --memory-limit=1G",
  "test": "vendor/bin/pest",
  "check": ["@lint", "@analyse", "@test"]
}
```
Add to `package.json`:
```json
"check": "npm run lint && npm run type-check && npm run build"
```
Both `composer check` and `npm run check` must pass.

Commit: `Add composer and npm check scripts`

### 1.5 Continuous integration
Create `.github/workflows/ci.yml`:
- Triggers on push to `main` and on pull requests.
- Services: `postgres:17` with `POSTGRES_DB=golden_court_test`, `POSTGRES_PASSWORD=postgres`, health check.
- Jobs (can be one job with steps or two parallel jobs):
  - PHP 8.4 with `pgsql`, `pdo_pgsql` extensions; `composer install`; `composer check`.
  - Node 22; `npm ci`; `npm run check`.
- Cache Composer and npm dependencies.

Commit: `Add CI running PHP and frontend checks against PostgreSQL`

### 1.6 Housekeeping
- Add `.editorconfig` if missing.
- Add a minimal `README.md` with: what the project is (two sentences), requirements, setup steps (Herd, database creation, `composer install`, `npm install`, `.env`, `php artisan key:generate`, `php artisan migrate --seed`), and how to run checks. The full README is written in Phase 8.
- Ensure `.gitignore` covers `.env`, `node_modules`, `vendor`, `public/build`, `.phpunit.result.cache`.

Commit: `Add README skeleton and editorconfig`

## Acceptance criteria

- [ ] `composer check` passes locally with zero PHPStan errors at level 8.
- [ ] `npm run check` passes locally.
- [ ] CI is green on `main`.
- [ ] Tests run against PostgreSQL (verify with `php artisan test` while the Postgres service is stopped — it should fail to connect, not silently use SQLite).
- [ ] Every PHP file starts with `declare(strict_types=1);`.
- [ ] No domain code has been added.
