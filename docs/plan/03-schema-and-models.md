# 03 — Schema and models

Goal: the persistence layer, with PostgreSQL enforcing the domain rules and every constraint proven by a test.

Conventions for this phase:
- One migration per table. Use `DB::statement` for CHECK constraints and any Postgres-specific DDL; Laravel's schema builder does not express them.
- Enums are stored as `string` columns constrained by a CHECK against the enum's values (generate the list from the PHP enum in the migration so they cannot drift). Do not use Postgres native enum types — they are painful to alter.
- Every table has `created_at`/`updated_at`. Soft deletes only where specified.
- Every model: `$fillable` explicit, `casts()` method, typed relationship return types, PHPStan-friendly `@property` docblocks are **not** needed if Larastan's model reflection works — verify.

## Tasks

### 3.1 Users
- Add to `users`: `role` string default `player` with CHECK, `display_name` string (public name shown on reviews; keep `name` for account), `bio` nullable text.
- `User` model casts `role` to `Role`. Add `isAdmin()`, `isVenueOwner()`.
- Update factory: `display_name`, random role weighted towards `player`.

Commit: `Add role and public profile fields to users`

### 3.2 Venues
`venues`:
- `id`, `name`, `slug` unique, `description` nullable text
- `address_line_1`, `address_line_2` nullable, `city`, `postcode`, `country_code` char(2) default `GB`
- `latitude` decimal(9,6), `longitude` decimal(9,6)
- `website` nullable, `phone` nullable
- `aggregate_score` decimal(2,1) default 0.0, `review_count` integer default 0 — denormalised, written only by the recalculation job
- `claimed_by_user_id` nullable FK to users (set in Phase 6)
- timestamps, soft deletes
- Index on `city`; index on `(latitude, longitude)`.
- Full-text: add a generated `search_vector tsvector` column over `name || ' ' || city` and a GIN index. Use `DB::statement`.

`Venue` model: `coordinates` via `CoordinatesCast`, `slug` generated from name on creating (observer or `booted()`), `courts()` hasMany, `owner()` belongsTo nullable.

Commit: `Add venues table with geo and full-text columns`

### 3.3 Courts
`courts`:
- `id`, `venue_id` FK cascade, `name` (e.g. "Court 1"), `slug` unique within venue → unique index on `(venue_id, slug)`
- `court_type`, `wall_type`, `surface` — strings with CHECK constraints against enum values
- `aggregate_score` decimal(2,1) default 0.0, `review_count` integer default 0
- timestamps, soft deletes

`Court` model: enum casts, `venue()` belongsTo, `reviews()` hasMany, `publishedReviews()` hasMany filtered by status.

Commit: `Add courts table`

### 3.4 Reviews
`reviews`:
- `id`, `user_id` FK cascade, `court_id` FK cascade
- `glass_rating`, `lighting_rating`, `turf_rating`, `facilities_rating` smallint
- `body` text
- `status` string default `pending` with CHECK
- `played_on` date nullable (when they played there)
- `helpful_count` integer default 0 — denormalised
- timestamps, soft deletes
- **Unique** on `(user_id, court_id)`.
- **CHECK** each rating BETWEEN 1 AND 5.
- **CHECK** `char_length(body) BETWEEN 20 AND 2000`.
- Index on `(court_id, status)`, index on `created_at`.

`Review` model: `RatingCast` on the four columns, `status` → `ReviewStatus`, `scores(): CourtScores` accessor building from the four ratings, relationships to user/court, `reply()` hasOne, `flags()` hasMany, `votes()` hasMany. Global scope is **not** used for status; use an explicit `published()` local scope.

Commit: `Add reviews table with uniqueness and range constraints`

### 3.5 Review replies, votes, flags
`review_replies`: `id`, `review_id` FK cascade **unique** (one reply per review), `user_id` FK, `body` text CHECK length 1–1000, timestamps.

`review_votes`: `id`, `review_id` FK cascade, `user_id` FK cascade, timestamps. Unique `(review_id, user_id)`.

`review_flags`: `id`, `review_id` FK cascade, `user_id` FK cascade, `reason` string CHECK against `FlagReason`, `details` nullable text, `resolved_at` nullable timestamp, `resolved_by_user_id` nullable FK, timestamps. Unique `(review_id, user_id)`.

Models with enum casts and relationships.

Commit: `Add review replies, votes and flags tables`

### 3.6 Venue claims
`venue_claims`: `id`, `venue_id` FK cascade, `user_id` FK cascade, `status` string default `pending` with CHECK, `evidence` text (how they prove ownership), `reviewed_by_user_id` nullable FK, `reviewed_at` nullable, `rejection_reason` nullable text, timestamps.
- Partial unique index: only one `pending` claim per venue: `CREATE UNIQUE INDEX ... ON venue_claims (venue_id) WHERE status = 'pending'`.

Model with `ClaimStatus` cast.

Commit: `Add venue claims table with single-pending-claim constraint`

### 3.7 Factories
Factories for every model. Requirements:
- Produce valid rows by default (respect all CHECKs).
- `VenueFactory` uses realistic UK cities and lat/long within the UK bounding box.
- `ReviewFactory` defaults to `published`; states `pending()`, `flagged()`, `removed()`.
- `UserFactory` states `admin()`, `venueOwner()`.
- Relationship-aware: `Court::factory()->for($venue)` etc.

Commit: `Add model factories`

### 3.8 Seeder
`DatabaseSeeder` creates: 1 admin, 1 venue owner, ~30 players, ~12 venues across several UK cities (hand-written realistic-sounding names, not real businesses) each with 2–6 courts, and 150–300 published reviews spread unevenly so some courts have many and some have none. Run the aggregate recalculation for every court and venue at the end (call the Phase 4 job synchronously; until Phase 4 exists, leave a TODO and set aggregates directly from a simple average in the seeder).

Commit: `Add development seeder with realistic UK venues`

### 3.9 Constraint tests
A Pest feature test file per table that proves each constraint fires:
- duplicate review for same user/court → `QueryException` with unique violation
- rating of 0 and 6 → check violation
- body of 19 characters → check violation
- invalid status string → check violation
- second pending claim for same venue → unique violation, while an approved + a pending coexist happily
- second reply on a review → unique violation

Assert on the SQLSTATE (`23505` unique, `23514` check) rather than message text.

Commit: `Prove database constraints with tests`

### 3.10 Cast tests
Finish the TODO from Phase 2: `Review->glass_rating` returns `Rating`; `Venue->coordinates` returns `Coordinates` and setting it writes both columns.

Commit: `Test Eloquent casts against real models`

## Acceptance criteria

- [ ] `php artisan migrate:fresh --seed` succeeds and produces browsable data.
- [ ] Every CHECK and UNIQUE constraint has a failing-case test that asserts the SQLSTATE.
- [ ] No model has business logic beyond casts, relationships, scopes and simple predicates.
- [ ] All enum columns have CHECK constraints generated from the PHP enum cases.
- [ ] `composer check` and `npm run check` pass.
