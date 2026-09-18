# 06 — Trust and ownership

Goal: the features that make a review platform credible — helpful votes, flagging, venue claims with admin approval, and owner replies. This phase is the best showcase for policies, state machines and role-based access.

## Tasks

### 6.1 Helpful votes
- `ToggleReviewVoteAction::handle(User $user, Review $review): bool` — creates or deletes the vote inside a transaction, updates `reviews.helpful_count` with an atomic `increment`/`decrement`, returns the new state. Handles the unique violation race by treating it as "already voted".
- `ReviewPolicy::vote`: authenticated, not the review's author, review is published.
- `POST /reviews/{review}/vote` → `ToggleReviewVoteController`.
- Tests: toggle on/off, author cannot vote, count stays consistent under repeated toggles.

Commit: `Add helpful votes on reviews`

### 6.2 Flagging
- `FlagReviewData`: `reason` (`FlagReason`), `details` nullable.
- `FlagReviewAction::handle(User $user, Review $review, FlagReviewData $data): ReviewFlag` — creates the flag; if the review now has ≥ 3 unresolved flags and is `published`, calls `ChangeReviewStatusAction` to move it to `flagged` (actor is a system/admin user — decide and document; a `SystemActor` null-object is acceptable).
- Threshold lives in `config/golden_court.php` as `moderation.auto_flag_threshold`.
- `ReviewPolicy::flag`: authenticated, not the author, not already flagged by this user, review is published.
- `POST /reviews/{review}/flags` → `FlagReviewController`.
- Tests including the threshold behaviour.

Commit: `Add review flagging with automatic threshold escalation`

### 6.3 Venue claims
- `SubmitVenueClaimData`: `evidence`.
- `SubmitVenueClaimAction::handle(User $user, Venue $venue, SubmitVenueClaimData $data): VenueClaim` — throws `VenueAlreadyClaimedException` if the venue has an owner; translates the partial-unique violation into `ClaimAlreadyPendingException`.
- `ReviewVenueClaimAction::handle(VenueClaim $claim, User $admin, ClaimStatus $decision, ?string $rejectionReason): VenueClaim` — validates `pending → approved|rejected` only; on approval sets `venues.claimed_by_user_id`, promotes the user's role to `venue_owner` if they are a `player`, all in one transaction; dispatches `VenueClaimApproved` / `VenueClaimRejected`.
- `VenueClaimPolicy`: `create` (authenticated, venue unclaimed), `review` (admin).
- Routes: `POST /venues/{venue:slug}/claims`, and admin routes in 6.5.
- Tests: full happy path, both exception paths, role promotion, invalid transition.

Commit: `Add venue claim submission and review workflow`

### 6.4 Owner replies
- `ReplyToReviewData`: `body`.
- `ReplyToReviewAction::handle(User $owner, Review $review, ReplyToReviewData $data): ReviewReply` — translates the one-reply-per-review unique violation into `ReplyAlreadyExistsException`.
- `UpdateReviewReplyAction`, `DeleteReviewReplyAction`.
- `ReviewReplyPolicy`: `create` — user owns the review's court's venue (`$review->court->venue->claimed_by_user_id === $user->id`) or is admin, review is published; `update`/`delete` — reply author or admin.
- Routes under `/reviews/{review}/reply`.
- Tests: owner of a *different* venue is forbidden; player is forbidden; admin allowed.

Commit: `Add venue owner replies to reviews`

### 6.5 Admin area
Route group `/admin` behind an `EnsureUserIsAdmin` middleware (or `can:access-admin` gate). Minimal Inertia pages:
- `GET /admin` — counts: pending claims, unresolved flags, pending reviews.
- `GET /admin/claims` — pending claims list; approve/reject actions.
- `GET /admin/flags` — reviews with unresolved flags; actions: publish (dismiss flags — set `resolved_at`), remove.
- `GET /admin/reviews/pending` — publish or remove.
- `ResolveReviewFlagsAction::handle(Review $review, User $admin, ReviewStatus $outcome)` — resolves all open flags and calls `ChangeReviewStatusAction`.
- Feature tests: non-admin gets 403 on every admin route; each action works end to end.

Commit: `Add admin area for claims and moderation`

### 6.6 Notifications (lightweight)
Database notifications only (no mail provider needed):
- Claimant notified on approval/rejection.
- Review author notified when their review is published, removed, or receives an owner reply.
- Listeners on the relevant events; `ShouldQueue`.
- Tests with `Notification::fake()`.

Commit: `Notify users of claim decisions and review outcomes`

### 6.7 Audit trail
`App\Domain\Moderation\Models\ModerationLog` — who did what to which review/claim and when, written by `ChangeReviewStatusAction`, `ReviewVenueClaimAction` and `ResolveReviewFlagsAction`. Append-only; no update/delete routes. Shown on the admin review detail.

Commit: `Record moderation actions in an append-only log`

## Acceptance criteria

- [ ] A claim can be submitted, approved by an admin, and the claimant can then reply to a review on that venue — proven by one end-to-end feature test.
- [ ] Three flags on a published review move it to `flagged` automatically; an admin can publish or remove it and all flags resolve.
- [ ] Every state transition that is invalid throws a domain exception and is tested.
- [ ] Every policy ability is tested for allowed and forbidden cases.
- [ ] `composer check` and `npm run check` pass.
