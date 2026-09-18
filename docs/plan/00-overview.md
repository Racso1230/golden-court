# 00 — Overview

## Product in one paragraph

Golden Court lets padel players find a venue, see its courts, read reviews from other players and leave their own. Each review scores a court on four dimensions (glass, lighting, turf, facilities) plus a written body. Courts and venues carry an aggregate score that is recalculated asynchronously. Venue owners can claim a venue, and once approved can post one public reply per review. Users can flag reviews and vote them helpful. A small admin area handles claims and flags. Nothing about booking, prices or availability.

The name is a play on padel's "golden point" — the single point that decides a game. The top-scoring court in a city carries a "Golden Court" badge.

## Why it is built this way

This is a portfolio project intended to demonstrate mid-level engineering judgement: SOLID applied pragmatically inside Laravel, strict typing across PHP and TypeScript, database constraints treated as part of the domain, asynchronous aggregation, authorisation via policies, and a test suite that documents behaviour. Every phase should leave the codebase in a state that could be reviewed by a senior engineer without embarrassment.

## Phases

| Phase | File | Outcome |
|---|---|---|
| 1 | `01-foundation.md` | Strict tooling, CI, quality scripts. No domain code. |
| 2 | `02-domain-primitives.md` | Value objects, enums, exceptions, folder structure. |
| 3 | `03-schema-and-models.md` | Migrations with constraints, models, factories, seeders. |
| 4 | `04-reviews.md` | Submit/edit/delete reviews, events, aggregate recalculation. |
| 5 | `05-discovery.md` | Venue and court listing, search, geo, sorting, filtering. |
| 6 | `06-trust-and-ownership.md` | Flags, helpful votes, venue claims, owner replies, admin area. |
| 7 | `07-frontend.md` | Inertia pages in Vue/TS with generated types. |
| 8 | `08-hardening-and-docs.md` | Rate limits, Bayesian aggregator, ARCHITECTURE.md, README. |

Phases 4–6 are backend-first with minimal Inertia pages so the flows are exercisable. Phase 7 builds the real UI on top. If a phase is too large for one session, split it at the numbered task boundaries — each task is self-contained.

## Domain vocabulary

Use these names consistently in code, tests and UI.

- **Venue** — a physical location with an address and one or more courts.
- **Court** — an individual playable court at a venue. Has a type (indoor / outdoor / covered), a wall type (panoramic / classic) and a surface.
- **Review** — one user's assessment of one court: four `Rating` values and a body. One per user per court, enforced by the database.
- **Rating** — an integer 1–5. Value object.
- **CourtScores** — the four ratings of a review together, with a derived overall. Value object.
- **AggregateScore** — the computed score for a court (and, rolled up, a venue), with the review count it was computed from. Value object.
- **RatingAggregator** — interface that turns a set of reviews into an `AggregateScore`. Two implementations: simple average and Bayesian.
- **ReviewFlag** — a user reporting a review for moderation.
- **ReviewVote** — a user marking a review helpful.
- **VenueClaim** — a request by a user to become the owner of a venue. Has a status.
- **ReviewReply** — a venue owner's single public response to a review.
- **Role** — `player`, `venue_owner`, `admin`. Backed enum on the user.

## Bounded contexts

```
App\Domain\Users
App\Domain\Venues
App\Domain\Courts
App\Domain\Reviews
App\Domain\Claims
App\Domain\Moderation
```

Cross-context communication goes through events and listeners, not direct model calls where avoidable.

## Working agreement with Claude Code

- Read `CLAUDE.md` first, then this file, then the phase file.
- Complete tasks in order. After each task, run `composer check` and `npm run check`.
- Commit after each task with the suggested commit message (or a better one in the same style).
- At the end of a phase, confirm every acceptance criterion and report anything that could not be met rather than working around it.
- Ask before adding a package not listed in the phase.
