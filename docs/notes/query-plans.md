# Venue search query plans

Captured on 2026-09-18 against PostgreSQL 17.11 with `EXPLAIN (ANALYZE, BUFFERS)`.
The table held 5,000 factory-generated venues (`Venue::factory()->count(5000)`,
then `ANALYZE venues`) so the planner had realistic statistics to work with.
The SQL is exactly what `App\Domain\Venues\Queries\VenueSearchQuery` produces
(`->toBuilder()->limit(20)->toRawSql()`).

## What the plans show

- **Proximity** uses the functional GiST index `venues_location_earth_index`
  on `ll_to_earth(latitude, longitude)`. The `earth_box(...) @> ll_to_earth(...)`
  predicate becomes a Bitmap Index Scan that returns only the candidates inside
  the bounding box; the exact `earth_distance(...) <= radius` check then runs
  on those rows alone.
- **Full-text** uses the GIN index `venues_search_vector_index` on the
  generated `search_vector` column whenever the term is selective enough
  (see the two-word term below, and the forced-index run). For a single common
  word matching ~6% of a 5,000-row table the planner correctly prefers a
  sequential scan: the whole table is 241 buffers, so the index would not save
  anything. On a larger table the crossover flips on its own.
- **Combined** term + proximity uses the GiST index for the geographic
  bounding box and applies the full-text predicate as a filter on the small
  candidate set.
- The `courts_count` subselect is answered from `courts_venue_id_slug_unique`,
  so it costs one index probe per returned row, never a scan.

Why earthdistance rather than PostGIS: it ships with every PostgreSQL build
including the `postgres:17` CI image, great-circle distance is all this
product needs, and it avoids a heavier dependency. `docs/ARCHITECTURE.md`
(Phase 8) records the decision.

## Raw plans

## Full-text term only

```sql
select "venues".*, (select count(*) from "courts" where "venues"."id" = "courts"."venue_id" and "courts"."deleted_at" is null) as "courts_count" from "venues" where venues.search_vector @@ plainto_tsquery('english', 'manchester') and "venues"."deleted_at" is null order by "venues"."aggregate_score" desc, "venues"."review_count" desc, ts_rank(venues.search_vector, plainto_tsquery('english', 'manchester')) DESC, "venues"."id" asc limit 20
```

```
Limit  (cost=312.82..476.47 rows=20 width=448) (actual time=1.202..1.214 rows=20 loops=1)
  Buffers: shared hit=284
  ->  Result  (cost=312.82..2931.22 rows=320 width=448) (actual time=1.199..1.211 rows=20 loops=1)
        Buffers: shared hit=284
        ->  Sort  (cost=312.82..313.62 rows=320 width=440) (actual time=1.189..1.190 rows=20 loops=1)
              Sort Key: venues.aggregate_score DESC, venues.review_count DESC, (ts_rank(venues.search_vector, '''manchest'''::tsquery)) DESC, venues.id
              Sort Method: top-N heapsort  Memory: 35kB
              Buffers: shared hit=244
              ->  Seq Scan on venues  (cost=0.00..304.30 rows=320 width=440) (actual time=0.018..0.934 rows=320 loops=1)
                    Filter: ((deleted_at IS NULL) AND (search_vector @@ '''manchest'''::tsquery))
                    Rows Removed by Filter: 4680
                    Buffers: shared hit=241
        SubPlan 1
          ->  Aggregate  (cost=8.16..8.17 rows=1 width=8) (actual time=0.000..0.000 rows=1 loops=20)
                Buffers: shared hit=40
                ->  Index Scan using courts_venue_id_slug_unique on courts  (cost=0.14..8.15 rows=1 width=0) (actual time=0.000..0.000 rows=0 loops=20)
                      Index Cond: (venue_id = venues.id)
                      Filter: (deleted_at IS NULL)
                      Buffers: shared hit=40
Planning:
  Buffers: shared hit=172
Planning Time: 1.731 ms
Execution Time: 1.252 ms
```

## Proximity only (25 km around Manchester)

```sql
select "venues".*, (select count(*) from "courts" where "venues"."id" = "courts"."venue_id" and "courts"."deleted_at" is null) as "courts_count", earth_distance(ll_to_earth(53.4808, -2.2426), ll_to_earth(venues.latitude::float8, venues.longitude::float8)) / 1000 AS distance_km from "venues" where earth_box(ll_to_earth(53.4808, -2.2426), 25000) @> ll_to_earth(venues.latitude::float8, venues.longitude::float8) and earth_distance(ll_to_earth(53.4808, -2.2426), ll_to_earth(venues.latitude::float8, venues.longitude::float8)) <= 25000 and "venues"."deleted_at" is null order by earth_distance(ll_to_earth(53.4808, -2.2426), ll_to_earth(venues.latitude::float8, venues.longitude::float8)) ASC, "venues"."id" asc limit 20
```

```
Limit  (cost=26.94..45.33 rows=2 width=460) (actual time=2.241..2.254 rows=20 loops=1)
  Buffers: shared hit=226
  ->  Result  (cost=26.94..45.33 rows=2 width=460) (actual time=2.241..2.252 rows=20 loops=1)
        Buffers: shared hit=226
        ->  Sort  (cost=26.94..26.94 rows=2 width=444) (actual time=2.229..2.230 rows=20 loops=1)
              Sort Key: (sec_to_gc(cube_distance('(3792690.516274298, -148524.62000820693, 5125862.47584135)'::cube, (ll_to_earth((venues.latitude)::double precision, (venues.longitude)::double precision))::cube))), venues.id
              Sort Method: top-N heapsort  Memory: 40kB
              Buffers: shared hit=186
              ->  Bitmap Heap Scan on venues  (cost=4.19..26.93 rows=2 width=444) (actual time=0.226..2.109 rows=320 loops=1)
                    Recheck Cond: ('(3767690.532277865, -173524.60400463993, 5100862.491844917),(3817690.5002707313, -123524.63601177392, 5150862.459837783)'::cube @> (ll_to_earth((latitude)::double precision, (longitude)::double precision))::cube)
                    Filter: ((deleted_at IS NULL) AND (sec_to_gc(cube_distance('(3792690.516274298, -148524.62000820693, 5125862.47584135)'::cube, (ll_to_earth((latitude)::double precision, (longitude)::double precision))::cube)) <= '25000'::double precision))
                    Heap Blocks: exact=177
                    Buffers: shared hit=183
                    ->  Bitmap Index Scan on venues_location_earth_index  (cost=0.00..4.18 rows=5 width=0) (actual time=0.035..0.035 rows=320 loops=1)
                          Index Cond: ((ll_to_earth((latitude)::double precision, (longitude)::double precision))::cube <@ '(3767690.532277865, -173524.60400463993, 5100862.491844917),(3817690.5002707313, -123524.63601177392, 5150862.459837783)'::cube)
                          Buffers: shared hit=6
        SubPlan 1
          ->  Aggregate  (cost=8.16..8.17 rows=1 width=8) (actual time=0.001..0.001 rows=1 loops=20)
                Buffers: shared hit=40
                ->  Index Scan using courts_venue_id_slug_unique on courts  (cost=0.14..8.15 rows=1 width=0) (actual time=0.001..0.001 rows=0 loops=20)
                      Index Cond: (venue_id = venues.id)
                      Filter: (deleted_at IS NULL)
                      Buffers: shared hit=40
Planning:
  Buffers: shared hit=37
Planning Time: 0.771 ms
Execution Time: 2.614 ms
```

## Term and proximity combined

```sql
select "venues".*, (select count(*) from "courts" where "venues"."id" = "courts"."venue_id" and "courts"."deleted_at" is null) as "courts_count", earth_distance(ll_to_earth(53.4808, -2.2426), ll_to_earth(venues.latitude::float8, venues.longitude::float8)) / 1000 AS distance_km from "venues" where venues.search_vector @@ plainto_tsquery('english', 'padel') and earth_box(ll_to_earth(53.4808, -2.2426), 25000) @> ll_to_earth(venues.latitude::float8, venues.longitude::float8) and earth_distance(ll_to_earth(53.4808, -2.2426), ll_to_earth(venues.latitude::float8, venues.longitude::float8)) <= 25000 and "venues"."deleted_at" is null order by earth_distance(ll_to_earth(53.4808, -2.2426), ll_to_earth(venues.latitude::float8, venues.longitude::float8)) ASC, ts_rank(venues.search_vector, plainto_tsquery('english', 'padel')) DESC, "venues"."id" asc limit 20
```

```
Limit  (cost=26.44..35.65 rows=1 width=464) (actual time=1.658..1.670 rows=20 loops=1)
  Buffers: shared hit=223
  ->  Result  (cost=26.44..35.65 rows=1 width=464) (actual time=1.658..1.670 rows=20 loops=1)
        Buffers: shared hit=223
        ->  Sort  (cost=26.44..26.45 rows=1 width=448) (actual time=1.650..1.651 rows=20 loops=1)
              Sort Key: (sec_to_gc(cube_distance('(3792690.516274298, -148524.62000820693, 5125862.47584135)'::cube, (ll_to_earth((venues.latitude)::double precision, (venues.longitude)::double precision))::cube))), (ts_rank(venues.search_vector, '''padel'''::tsquery)) DESC, venues.id
              Sort Method: top-N heapsort  Memory: 42kB
              Buffers: shared hit=183
              ->  Bitmap Heap Scan on venues  (cost=4.19..26.43 rows=1 width=448) (actual time=0.167..1.569 rows=243 loops=1)
                    Recheck Cond: ('(3767690.532277865, -173524.60400463993, 5100862.491844917),(3817690.5002707313, -123524.63601177392, 5150862.459837783)'::cube @> (ll_to_earth((latitude)::double precision, (longitude)::double precision))::cube)
                    Filter: ((deleted_at IS NULL) AND (search_vector @@ '''padel'''::tsquery) AND (sec_to_gc(cube_distance('(3792690.516274298, -148524.62000820693, 5125862.47584135)'::cube, (ll_to_earth((latitude)::double precision, (longitude)::double precision))::cube)) <= '25000'::double precision))
                    Rows Removed by Filter: 77
                    Heap Blocks: exact=177
                    Buffers: shared hit=183
                    ->  Bitmap Index Scan on venues_location_earth_index  (cost=0.00..4.18 rows=5 width=0) (actual time=0.021..0.021 rows=320 loops=1)
                          Index Cond: ((ll_to_earth((latitude)::double precision, (longitude)::double precision))::cube <@ '(3767690.532277865, -173524.60400463993, 5100862.491844917),(3817690.5002707313, -123524.63601177392, 5150862.459837783)'::cube)
                          Buffers: shared hit=6
        SubPlan 1
          ->  Aggregate  (cost=8.16..8.17 rows=1 width=8) (actual time=0.001..0.001 rows=1 loops=20)
                Buffers: shared hit=40
                ->  Index Scan using courts_venue_id_slug_unique on courts  (cost=0.14..8.15 rows=1 width=0) (actual time=0.000..0.000 rows=0 loops=20)
                      Index Cond: (venue_id = venues.id)
                      Filter: (deleted_at IS NULL)
                      Buffers: shared hit=40
Planning:
  Buffers: shared hit=3
Planning Time: 0.400 ms
Execution Time: 1.718 ms
```

## Selective two-word term ("harbour manchester")

```sql
select "venues".*, (select count(*) from "courts" where "venues"."id" = "courts"."venue_id" and "courts"."deleted_at" is null) as "courts_count" from "venues" where venues.search_vector @@ plainto_tsquery('english', 'harbour manchester') and "venues"."deleted_at" is null order by "venues"."aggregate_score" desc, "venues"."review_count" desc, ts_rank(venues.search_vector, plainto_tsquery('english', 'harbour manchester')) DESC, "venues"."id" asc limit 20
```

```
Limit  (cost=84.66..248.31 rows=20 width=448) (actual time=0.257..0.269 rows=20 loops=1)
  Buffers: shared hit=77
  ->  Result  (cost=84.66..281.04 rows=24 width=448) (actual time=0.255..0.266 rows=20 loops=1)
        Buffers: shared hit=77
        ->  Sort  (cost=84.66..84.72 rows=24 width=440) (actual time=0.242..0.242 rows=20 loops=1)
              Sort Key: venues.aggregate_score DESC, venues.review_count DESC, (ts_rank(venues.search_vector, '''harbour'' & ''manchest'''::tsquery)) DESC, venues.id
              Sort Method: quicksort  Memory: 35kB
              Buffers: shared hit=37
              ->  Bitmap Heap Scan on venues  (cost=13.07..84.11 rows=24 width=440) (actual time=0.085..0.152 rows=22 loops=1)
                    Recheck Cond: (search_vector @@ '''harbour'' & ''manchest'''::tsquery)
                    Filter: (deleted_at IS NULL)
                    Heap Blocks: exact=20
                    Buffers: shared hit=25
                    ->  Bitmap Index Scan on venues_search_vector_index  (cost=0.00..13.06 rows=24 width=0) (actual time=0.065..0.065 rows=22 loops=1)
                          Index Cond: (search_vector @@ '''harbour'' & ''manchest'''::tsquery)
                          Buffers: shared hit=5
        SubPlan 1
          ->  Aggregate  (cost=8.16..8.17 rows=1 width=8) (actual time=0.001..0.001 rows=1 loops=20)
                Buffers: shared hit=40
                ->  Index Scan using courts_venue_id_slug_unique on courts  (cost=0.14..8.15 rows=1 width=0) (actual time=0.000..0.000 rows=0 loops=20)
                      Index Cond: (venue_id = venues.id)
                      Filter: (deleted_at IS NULL)
                      Buffers: shared hit=40
Planning:
  Buffers: shared hit=420
Planning Time: 6.180 ms
Execution Time: 0.356 ms
```

## Term "manchester" with sequential scans disabled

```
Limit  (cost=277.39..441.04 rows=20 width=448) (actual time=0.715..0.727 rows=20 loops=1)
  Buffers: shared hit=220
  ->  Result  (cost=277.39..2895.79 rows=320 width=448) (actual time=0.714..0.726 rows=20 loops=1)
        Buffers: shared hit=220
        ->  Sort  (cost=277.39..278.19 rows=320 width=440) (actual time=0.707..0.708 rows=20 loops=1)
              Sort Key: venues.aggregate_score DESC, venues.review_count DESC, (ts_rank(venues.search_vector, '''manchest'''::tsquery)) DESC, venues.id
              Sort Method: top-N heapsort  Memory: 35kB
              Buffers: shared hit=180
              ->  Bitmap Heap Scan on venues  (cost=10.21..268.87 rows=320 width=440) (actual time=0.044..0.512 rows=320 loops=1)
                    Recheck Cond: (search_vector @@ '''manchest'''::tsquery)
                    Filter: (deleted_at IS NULL)
                    Heap Blocks: exact=177
                    Buffers: shared hit=180
                    ->  Bitmap Index Scan on venues_search_vector_index  (cost=0.00..10.13 rows=320 width=0) (actual time=0.024..0.024 rows=320 loops=1)
                          Index Cond: (search_vector @@ '''manchest'''::tsquery)
                          Buffers: shared hit=3
        SubPlan 1
          ->  Aggregate  (cost=8.16..8.17 rows=1 width=8) (actual time=0.001..0.001 rows=1 loops=20)
                Buffers: shared hit=40
                ->  Index Scan using courts_venue_id_slug_unique on courts  (cost=0.14..8.15 rows=1 width=0) (actual time=0.000..0.000 rows=0 loops=20)
                      Index Cond: (venue_id = venues.id)
                      Filter: (deleted_at IS NULL)
                      Buffers: shared hit=40
Planning:
  Buffers: shared hit=3
Planning Time: 0.144 ms
Execution Time: 0.766 ms
```
