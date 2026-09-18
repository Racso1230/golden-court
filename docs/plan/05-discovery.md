# 05 — Discovery

Goal: players can find venues by name, city or proximity, and browse courts sorted and filtered sensibly. This phase shows off PostgreSQL and read-side design: query objects, pagination, and keeping heavy queries out of controllers.

## Tasks

### 5.1 Query objects
Introduce `App\Domain\Venues\Queries\VenueSearchQuery` as a builder-wrapping class (not a static helper):
- Constructor takes a `VenueSearchCriteria` Data object: `term` nullable, `city` nullable, `near` nullable `Coordinates`, `radiusKm` default 25, `courtType` nullable, `wallType` nullable, `surface` nullable, `minScore` nullable, `sort` enum (`score`, `reviews`, `distance`, `name`), `page`.
- Exposes `paginate(int $perPage = 20): LengthAwarePaginator`.
- Composes: full-text match on `search_vector` when `term` is set (use `plainto_tsquery('english', ?)` and order by `ts_rank` as a tiebreak); city equality (case-insensitive); distance filter and computed `distance_km` column when `near` is set; court-attribute filters via `whereHas('courts', ...)`; `minScore` on `aggregate_score`; sort mapping.
- Distance: use the `earthdistance`/`cube` extensions **or** PostGIS `ST_DWithin` on a geography column — pick one, enable the extension in a migration, and note the choice in `docs/ARCHITECTURE.md` (Phase 8). If PostGIS is chosen, add a generated `location geography(Point,4326)` column on `venues` with a GIST index.

Tests: each criterion in isolation against seeded data, plus combinations, plus a distance test with two venues at known coordinates.

Commit: `Add VenueSearchQuery with full-text and geo filtering`

### 5.2 Read models
Data objects for the frontend (spatie/laravel-data):
- `VenueSummaryData` — id, name, slug, city, aggregate score, review count, court count, distance (nullable), golden court badge flag.
- `VenueDetailData` — everything above plus address, description, website, courts as `CourtSummaryData[]`, owner display name if claimed.
- `CourtSummaryData` — id, name, slug, type/wall/surface labels, aggregate, review count.
- `CourtDetailData` — plus paginated `ReviewData[]` and the per-dimension averages (glass/lighting/turf/facilities) computed in SQL with `AVG()`.

Commit: `Add venue and court read models`

### 5.3 Golden Court badge
- `App\Domain\Courts\Queries\GoldenCourtQuery::forCity(string $city): ?Court` — highest `aggregate_score` with at least 5 reviews, tiebreak on review count. Cache per city for 10 minutes; bust in `RecalculateCourtScore` via a tag or explicit `forget`.
- Expose on `CourtSummaryData` as `isGoldenCourt`.

Commit: `Add Golden Court badge query`

### 5.4 HTTP + minimal pages
Single-action controllers under `App\Http\Controllers\Discovery\`:
- `GET /venues` → `VenueIndexController` → `Venues/Index.vue` (search form + results list, pagination)
- `GET /venues/{venue:slug}` → `VenueShowController` → `Venues/Show.vue`
- `GET /venues/{venue:slug}/courts/{court:slug}` → `CourtShowController` → `Courts/Show.vue` (reviews sorted by `recent`/`helpful`/`highest`/`lowest`, paginated)
- Court slug binding scoped to venue (`scopeBindings()`).
- `VenueSearchRequest` validates and builds `VenueSearchCriteria`.

Feature tests: each route renders the right Inertia component with the right props (`assertInertia`), search params round-trip, unknown slug 404s, court from a different venue 404s.

Commit: `Add venue and court discovery routes`

### 5.5 Home page
`GET /` → top venues by score (min 10 reviews), recent reviews, and a search box. Simple.

Commit: `Add home page with top venues and recent reviews`

### 5.6 Performance sanity
- Eager-load everything the read models need; add a test using `DB::enableQueryLog()` or the `preventLazyLoading` flag (`Model::preventLazyLoading()` is already on via `shouldBeStrict`) to guarantee no N+1 on the index page with 20 venues.
- Add `EXPLAIN` output for the search query with a term and a `near` filter to `docs/notes/query-plans.md` showing the GIN and GIST/earthdistance indexes are used.

Commit: `Verify discovery queries use indexes and avoid N+1`

## Acceptance criteria

- [ ] Searching "manchester" returns Manchester venues via full-text; searching by coordinates returns venues ordered by distance with a `distance_km` value.
- [ ] Filters compose without breaking pagination.
- [ ] No lazy-loading violation is raised on any discovery page with seeded data.
- [ ] Query objects have no knowledge of HTTP; controllers have no knowledge of SQL.
- [ ] `composer check` and `npm run check` pass.
