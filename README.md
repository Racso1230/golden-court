# Golden Court

Golden Court is a Trustpilot-style review hub for padel courts. Players find a
venue, see its courts, read what other players thought and leave their own
review, scoring each court on glass, lighting, turf and facilities. Venue
owners can claim a venue and reply to reviews once an admin approves the
claim. There is no booking, pricing or availability, and none is planned.

The name is a play on padel's **golden point**, the single point that decides
a game. The top-scoring court in each city, with at least five reviews,
carries the Golden Court badge.

## Features

- Search venues by name or city (PostgreSQL full-text) or by distance from a
  point (`earthdistance`), filter by court type, wall type, surface and
  minimum score.
- Four-dimension reviews with a written body; one review per player per
  court, enforced by the database.
- Court and venue scores recalculated by queued jobs through a swappable
  aggregator: a Bayesian average by default, a simple mean if you prefer.
- Helpful votes, flagging with automatic escalation after three flags, and an
  admin area for claims and moderation with an append-only audit log.
- Venue claims with admin approval, owner replies, database notifications.
- Strict typing end to end: PHP `strict_types` and Larastan level 8, Data
  classes generating the TypeScript types the Vue pages use.

## Stack

PHP 8.4 · Laravel 13 · PostgreSQL 17 · Inertia 3 (SSR) · Vue 3 · TypeScript ·
Tailwind CSS 4 · shadcn-vue · spatie/laravel-data ·
spatie/laravel-typescript-transformer · Pest · Larastan · Pint · Vitest ·
vite-plus (oxlint, oxfmt)

## Requirements

- PHP 8.4 with `pgsql` and `pdo_pgsql`
- Composer 2, Node 22 and npm
- PostgreSQL 17 (the `cube` and `earthdistance` contrib extensions ship with
  every build; the migrations enable them)
- [Laravel Herd](https://herd.laravel.com) for local serving (optional)

## Local setup

```sh
git clone git@github.com:Racso1230/golden-court.git
cd golden-court

createdb golden_court
createdb golden_court_test

composer install
npm install

cp .env.example .env          # already points at 127.0.0.1:5432, postgres/postgres
php artisan key:generate
php artisan migrate --seed    # 12 fictional UK venues, ~300 reviews

npm run dev                   # Vite dev server
php artisan queue:work        # scores are recalculated by queued jobs
```

Seeded logins, password `password`:

| Role | Email |
| --- | --- |
| Admin | `admin@goldencourt.test` |
| Venue owner (owns the first venue) | `owner@goldencourt.test` |

Herd serves the project at `http://golden-court.test`. Without Herd,
`php artisan serve` works too.

### Queue, schedule and health

- Score recalculation and notifications run on the `database` queue. Run
  `php artisan queue:work` locally; failed jobs land in `failed_jobs`
  (`php artisan queue:failed`, `queue:retry`).
- Soft-deleted reviews and resolved flags are pruned daily by `model:prune`
  via the scheduler; run `php artisan schedule:work` locally if you want it.
- `GET /up` is the health check and fails if the database is unreachable.

### Switching the scoring strategy

```sh
GOLDEN_COURT_AGGREGATION=simple        # default: bayesian
GOLDEN_COURT_BAYESIAN_CONFIDENCE=5
php artisan golden-court:recalculate-scores
```

## Checks

Both must pass before a change is done. CI runs them against a
`postgres:17` service on every push and pull request.

```sh
composer check   # Pint, PHPStan level 8, Pest against PostgreSQL
npm run check    # lint, format, build, SSR build, generated types, vue-tsc, Vitest
```

Individual steps: `composer lint`, `composer analyse`, `composer test`,
`npm run lint`, `npm run format:check`, `npm run build`, `npm run build:ssr`,
`npm run types`, `npm run type-check`, `npm run test`.

## Server-side rendering and SEO

The public pages (home, venue listing, venue and court pages) are rendered
on the server by Inertia; everything behind a login renders in the browser.

- **Development**: with `npm run dev` running, Laravel posts each public page
  to the Vite dev server, which renders it. Nothing else to start.
- **Production-like**: stop the dev server, then
  `npm run build && npm run build:ssr` and `php artisan inertia:start-ssr`
  (a Node process on port 13714). Check it with `php artisan inertia:check-ssr`
  and stop it with `php artisan inertia:stop-ssr`. Production needs Node.
- If the renderer is down, pages fall back to client rendering unless
  `INERTIA_SSR_THROW_ON_ERROR=true` (the `.env.example` default, so local
  SSR bugs fail loudly). Pest always runs with SSR disabled.

Head tags (title, description, canonical, robots, Open Graph, Twitter and
JSON-LD) are built in PHP under `app/Support/Seo` and the page builders in
each domain's `Actions`, so they are identical with and without SSR and are
asserted in Pest. `/sitemap.xml` and `/robots.txt` are served by the app;
absolute URLs come from `APP_URL`, which should be `http://golden-court.test`
locally.

The frontend commands shell out to `php artisan` (Wayfinder routes and the
TypeScript transformer), so PHP 8.4 must be on your `PATH`.

## Documentation

- [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md): layering, value objects,
  database constraints, aggregation, search and geo, authorisation, type
  safety, testing and known limitations.
- [docs/notes/query-plans.md](docs/notes/query-plans.md): `EXPLAIN ANALYZE`
  evidence for the search indexes.
- [docs/plan/](docs/plan/00-overview.md): the phased build plan the project
  was built from, and [CLAUDE.md](CLAUDE.md), the standing engineering brief.

## Licence

[MIT](LICENSE.md).
