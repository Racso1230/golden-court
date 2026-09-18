# Golden Court

Golden Court is a Trustpilot-style review hub for padel courts. Players rate individual courts at a venue on glass, lighting, turf and facilities, and venue owners can claim their venue and reply to reviews.

There is no booking, pricing or availability functionality, and none is planned.

## Requirements

- PHP 8.4 with the `pgsql` and `pdo_pgsql` extensions
- Composer 2
- Node 22 and npm
- PostgreSQL 17
- [Laravel Herd](https://herd.laravel.com) for local serving (optional but assumed)

## Setup

1. Clone the repository into your Herd sites directory and let Herd serve it as `golden-court.test`.
2. Create the two databases the app and its test suite use:

   ```sh
   createdb golden_court
   createdb golden_court_test
   ```

3. Install dependencies:

   ```sh
   composer install
   npm install
   ```

4. Configure the environment:

   ```sh
   cp .env.example .env
   php artisan key:generate
   ```

   The example file already points at `127.0.0.1:5432` with the `postgres` / `postgres` credentials. Adjust `DB_USERNAME` and `DB_PASSWORD` if your server differs. Tests use the same credentials against `golden_court_test` (see `phpunit.xml`).

5. Migrate and seed:

   ```sh
   php artisan migrate --seed
   ```

6. Start the frontend dev server:

   ```sh
   npm run dev
   ```

## Running checks

Both scripts must pass before a change is considered done.

```sh
composer check   # Pint (style), PHPStan level 8, Pest against PostgreSQL
npm run check    # lint, format check, production build, vue-tsc
```

Individual steps are available as `composer lint`, `composer analyse`, `composer test`, `npm run lint`, `npm run format:check`, `npm run build` and `npm run type-check`.

The test suite refuses to run without PostgreSQL. It never falls back to SQLite.

## Project brief

Conventions, architecture rules and the phased build plan live in [CLAUDE.md](CLAUDE.md) and [docs/plan](docs/plan/00-overview.md).
