# 04 — Reviews: write path and aggregation

Goal: a player can submit, edit and delete a review; every change recalculates the court and venue aggregates through a queued job that uses the `RatingAggregator` contract. This phase is where Actions, DTOs, events and policies come together — it should be the cleanest code in the project.

Minimal Inertia pages are acceptable here (a form and a confirmation). The real UI is Phase 7.

## Tasks

### 4.1 Data objects
Using spatie/laravel-data (install it in this task):
- `App\Domain\Reviews\Data\SubmitReviewData`: `courtId`, `glass`, `lighting`, `turf`, `facilities` (ints), `body`, `playedOn` (nullable `CarbonImmutable`). Add a `scores(): CourtScores` method.
- `App\Domain\Reviews\Data\UpdateReviewData`: same fields minus `courtId`.
- `App\Domain\Reviews\Data\ReviewData`: the read model sent to the frontend — id, scores (as array shape), overall, body, status, author display name, created/updated, helpful count, whether the current user has voted, optional reply. Use `Lazy` or explicit `from()` factories as needed.

Commit: `Add review Data objects`

### 4.2 Events
- `App\Domain\Reviews\Events\ReviewSubmitted(int $reviewId, int $courtId)`
- `App\Domain\Reviews\Events\ReviewUpdated(int $reviewId, int $courtId)`
- `App\Domain\Reviews\Events\ReviewDeleted(int $courtId)`
- `App\Domain\Reviews\Events\ReviewStatusChanged(int $reviewId, int $courtId, ReviewStatus $from, ReviewStatus $to)`

Events carry ids, not models, so they serialise cleanly onto the queue.

Commit: `Add review domain events`

### 4.3 Actions
Each is a `final` class with constructor-injected dependencies and a single `handle()`.

- `SubmitReviewAction::handle(User $user, SubmitReviewData $data): Review`
  - Wraps in `DB::transaction`.
  - Creates the review with status `pending` (auto-publishing rules come in 4.6).
  - Catches the unique-violation `QueryException` and rethrows `App\Domain\Reviews\Exceptions\ReviewAlreadyExistsException` — the DB is the guard; the exception is the translation.
  - Dispatches `ReviewSubmitted` after commit (`DB::afterCommit` or `dispatch()->afterCommit()`).
- `UpdateReviewAction::handle(Review $review, UpdateReviewData $data): Review` — dispatches `ReviewUpdated`.
- `DeleteReviewAction::handle(Review $review): void` — soft delete, dispatches `ReviewDeleted`.
- `ChangeReviewStatusAction::handle(Review $review, ReviewStatus $to, User $actor): Review` — validates the transition (`pending→published`, `published→flagged`, `flagged→published|removed`, `pending→removed`), throws `InvalidStatusTransitionException` otherwise, dispatches `ReviewStatusChanged`.

Tests for each action, including the exception paths and that events are dispatched (`Event::fake()`).

Commit: `Add review write Actions`

### 4.4 Aggregate recalculation
- `App\Domain\Reviews\Jobs\RecalculateCourtScore(int $courtId)` — `ShouldQueue`, `ShouldBeUnique` keyed on court id, `$tries = 3`.
  - Loads the court's published reviews' `CourtScores`, passes them to `RatingAggregator`, writes `aggregate_score` and `review_count` on the court in a single `update()`.
  - Then dispatches `RecalculateVenueScore` for the court's venue.
- `App\Domain\Reviews\Jobs\RecalculateVenueScore(int $venueId)` — venue aggregate is the review-count-weighted mean of its courts' aggregates; writes `aggregate_score` and `review_count` (sum).
- `App\Domain\Reviews\Listeners\QueueScoreRecalculation` handles all four events and dispatches `RecalculateCourtScore`. Register in `EventServiceProvider` or via attribute discovery.
- Queue connection: `database` locally (add the jobs table migration). Document `php artisan queue:work` in the README.

Tests: job computes the right values with a fake aggregator bound in the container; listener dispatches the job; venue rollup weights correctly; removed/pending reviews are excluded.

Commit: `Recalculate court and venue scores asynchronously on review changes`

### 4.5 Policies
`App\Domain\Reviews\Policies\ReviewPolicy`:
- `create(User $user, Court $court)`: user is a `player` or `admin` and has no existing review for that court (this is a UX pre-check; the DB still enforces).
- `update(User $user, Review $review)`: owner and review is `pending` or `published`, or admin.
- `delete(User $user, Review $review)`: owner or admin.
- `changeStatus(User $user, Review $review)`: admin only.

Register via `Gate::policy` in `DomainServiceProvider`. Tests for each ability.

Commit: `Add ReviewPolicy`

### 4.6 Auto-publish rule
Behind an interface so it can be swapped: `App\Domain\Reviews\Contracts\ReviewPublicationRule` with `shouldAutoPublish(User $user, Review $review): bool`. Default implementation `VerifiedUserAutoPublishRule`: publish immediately if the user's email is verified and they have no `removed` reviews; otherwise stay `pending` for moderation. Call it from `SubmitReviewAction` and dispatch `ReviewStatusChanged` when it flips to published.

Commit: `Add pluggable auto-publish rule for new reviews`

### 4.7 HTTP layer (minimal)
- Form Requests: `SubmitReviewRequest`, `UpdateReviewRequest` — validation rules mirror the DB constraints; `toData()` method returning the Data object.
- Controllers under `App\Http\Controllers\Reviews\`: `StoreReviewController`, `UpdateReviewController`, `DestroyReviewController` — single-action, `__invoke`, authorise via policy, call the Action, redirect with flash. Translate `ReviewAlreadyExistsException` into a validation error on `court_id`.
- Routes under `auth` + `verified` middleware, rate-limited (`throttle:reviews`, defined in Phase 8; use `throttle:10,1` for now).
- One Inertia page `Reviews/Create.vue` with the four rating inputs and body, wired to the store route. Plain Tailwind is fine.

Feature tests: happy path, validation failures, duplicate review returns a validation error, unauthenticated redirect, unverified user forbidden.

Commit: `Expose review submission, update and deletion over HTTP`

### 4.8 Seeder update
Replace the Phase 3 TODO: after seeding reviews, run `RecalculateCourtScore` synchronously for every court (`dispatchSync`).

Commit: `Use recalculation job in seeder`

## Acceptance criteria

- [ ] Submitting a review through the UI creates it, and after the queue worker runs, the court and venue aggregates update.
- [ ] A second review on the same court by the same user is rejected with a friendly validation message, and the test proves the DB constraint (not just the policy) is what stopped it.
- [ ] Swapping `RatingAggregator` to a stub in a test changes the computed aggregate — proving the abstraction is real.
- [ ] Every Action, Job, Listener and Policy has tests.
- [ ] Controllers contain no logic beyond authorise → data → action → response.
- [ ] `composer check` and `npm run check` pass.
