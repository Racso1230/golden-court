# 08 — Hardening and documentation

Goal: the details that make a reviewer trust the project — abuse controls, the second aggregator implementation that justifies the abstraction, and documentation that explains the decisions.

## Tasks

### 8.1 Bayesian aggregator
- `App\Domain\Reviews\Aggregators\BayesianAggregator` implementing `RatingAggregator`:
  `score = (C × m + Σ overall) / (C + n)` where `m` is the site-wide mean overall (injected via a `SiteStatistics` contract with a cached implementation) and `C` is a confidence constant from `config/golden_court.php` (`aggregation.bayesian_confidence`, default 5).
- Bind it as the default in `DomainServiceProvider`, controlled by `config('golden_court.aggregation.strategy')` = `simple|bayesian`.
- Tests: a court with one 5★ review scores below a court with twenty 4.6★ reviews; with `C = 0` it equals the simple average; empty input returns `AggregateScore::empty()`.
- Add a `php artisan golden-court:recalculate-scores` command that re-queues every court, for use after changing strategy.

Commit: `Add Bayesian rating aggregator and recalculation command`

### 8.2 Rate limiting
In `AppServiceProvider` (or a dedicated `RateLimitServiceProvider`) define named limiters:
- `reviews`: 5 per day per user.
- `flags`: 20 per hour per user.
- `claims`: 3 per day per user.
- `votes`: 60 per minute per user.
- `search`: 60 per minute per IP.
Apply to the corresponding routes. Test one limiter end to end with `RateLimiter::hit` or by looping requests.

Commit: `Add per-action rate limits`

### 8.3 Abuse controls
- Reviews from unverified emails never auto-publish (already), and unverified users cannot vote or flag.
- Minimum account age for reviewing: `config('golden_court.reviews.min_account_age_hours')`, default 1, checked in `ReviewPolicy::create`.
- Basic content check in `SubmitReviewAction`: reject bodies that are mostly URLs or a single repeated character via a `ReviewContentRule` contract with a `BasicHeuristicsRule` implementation (keep it simple; the point is the seam).

Commit: `Add basic anti-abuse rules behind contracts`

### 8.4 Observability
- Log every moderation action and claim decision at `info` with structured context (already in the audit log; mirror to the application log).
- Add Laravel Pulse **or** simply document the queue and failed-jobs table — pick the lighter option and note it.
- Add a `/up` health check (Laravel ships one) and confirm it checks the DB connection.

Commit: `Add structured logging for moderation and health check`

### 8.5 Database hygiene
- Review every migration for missing indexes on foreign keys and on columns used in `WHERE`/`ORDER BY` in the query objects. Add any missing.
- Add a scheduled command (`schedule:work` locally) that prunes soft-deleted reviews older than 90 days and resolved flags older than 180 days using `Prunable`.

Commit: `Index audit and pruning schedule`

### 8.6 ARCHITECTURE.md
Write `docs/ARCHITECTURE.md` covering, in this order, each in a few paragraphs:
1. Purpose and non-goals.
2. Layering: HTTP → Actions → Domain, and what is allowed to depend on what. Include a small Mermaid diagram.
3. Why value objects and where they live; why enums have CHECK constraints.
4. Why the database enforces uniqueness and ranges, and how Actions translate violations.
5. Aggregation: events → listener → queued job → `RatingAggregator`; why Bayesian; how to switch.
6. Search and geo: the chosen extension, the generated columns, the indexes, and the query-plan evidence from Phase 5.
7. Authorisation model: roles, policies, the venue-owner scope.
8. Type safety across the stack: strict_types, Larastan level 8, Data classes → generated TypeScript, and the CI staleness check.
9. Testing strategy and what each layer's tests are meant to prove.
10. Known limitations and what would change at 100× scale (read replicas, search service, materialised views).

Commit: `Write ARCHITECTURE.md`

### 8.7 README
Rewrite `README.md`:
- One-paragraph pitch and a screenshot or two.
- The "golden point" naming note.
- Feature list (short).
- Stack badges/list.
- Local setup (Herd, Postgres, seed, queue worker).
- `composer check` / `npm run check`.
- Link to `docs/ARCHITECTURE.md` and `docs/plan/`.
- Licence (MIT).

Commit: `Write README`

### 8.8 Final sweep
- `composer audit` and `npm audit` clean or documented.
- Remove any `TODO` left in code or turn it into a GitHub issue.
- Tag `v0.1.0`.

Commit: `Prepare v0.1.0`

## Acceptance criteria

- [ ] Switching `aggregation.strategy` and running the recalculation command changes scores site-wide with no code changes.
- [ ] All rate limiters are applied and at least one is tested.
- [ ] `ARCHITECTURE.md` and `README.md` are complete and accurate to the code as it exists.
- [ ] CI is green, `composer check` and `npm run check` pass, and the repository is tagged.
