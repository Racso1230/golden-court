# Changelog

## v0.1.0 — 2026-09-18

First complete build of Golden Court, delivered in eight phases.

- Strict tooling: PHP 8.4 `strict_types`, Larastan level 8, Pint, TypeScript
  `strict`, vite-plus lint and format, Vitest, CI against PostgreSQL 17.
- Domain primitives: `Rating`, `CourtScores`, `AggregateScore`, `Coordinates`
  value objects; backed enums with `CHECK` constraints generated from PHP.
- Schema with the database as the last line of defence: unique review per
  player per court, rating and length checks, single pending claim per venue,
  single reply per review; every constraint tested on SQLSTATE.
- Reviews: submit, edit, delete, auto-publish for verified users with a clean
  record, queued court and venue score recalculation through a swappable
  `RatingAggregator` (Bayesian by default, simple mean available).
- Discovery: full-text search on a generated `tsvector`, proximity search on
  `earthdistance` with a GiST index, Golden Court badge per city, home page.
- Trust and ownership: helpful votes, flagging with automatic escalation,
  venue claims with admin approval and role promotion, owner replies, admin
  area, database notifications, append-only moderation log.
- Frontend: TypeScript types generated from the PHP Data classes and checked
  for staleness in CI, keyboard-accessible rating input, optimistic voting,
  account and admin pages, axe-checked shared components.
- Hardening: named rate limiters, minimum account age, content heuristics,
  structured moderation logging, database-backed health check, index audit,
  daily pruning of soft-deleted reviews and resolved flags.

Known limitations are listed at the end of `docs/ARCHITECTURE.md`.
