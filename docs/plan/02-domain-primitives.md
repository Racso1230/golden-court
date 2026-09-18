# 02 — Domain primitives

Goal: the vocabulary of the domain exists as typed, tested PHP before any table or HTTP route touches it. Everything here is pure PHP with no framework dependency except where noted.

## Tasks

### 2.1 Folder structure
- Create the `app/Domain/*` layout described in `CLAUDE.md` for the contexts `Users`, `Venues`, `Courts`, `Reviews`, `Claims`, `Moderation`. Empty directories may hold a `.gitkeep`.
- Move `App\Models\User` to `App\Domain\Users\Models\User`. Update `config/auth.php`, `database/factories/UserFactory.php` (namespace and `$model`), and any starter-kit references. Run the full test suite.

Commit: `Introduce bounded-context folder structure and relocate User model`

### 2.2 Domain exception base
- `App\Domain\Shared\Exceptions\DomainException extends \DomainException` (the SPL one). All domain-specific exceptions extend this.
- `App\Domain\Reviews\Exceptions\InvalidRatingException extends DomainException` with a static constructor `outOfRange(int $value): self`.

Commit: `Add domain exception hierarchy`

### 2.3 Enums
All backed string enums, each with a `label(): string` method for UI display.

- `App\Domain\Users\Enums\Role`: `player`, `venue_owner`, `admin`.
- `App\Domain\Courts\Enums\CourtType`: `indoor`, `outdoor`, `covered`.
- `App\Domain\Courts\Enums\WallType`: `panoramic`, `classic`.
- `App\Domain\Courts\Enums\Surface`: `artificial_grass`, `carpet`, `concrete`, `other`.
- `App\Domain\Reviews\Enums\ReviewStatus`: `pending`, `published`, `flagged`, `removed`. Add `isVisible(): bool` returning true only for `published`.
- `App\Domain\Claims\Enums\ClaimStatus`: `pending`, `approved`, `rejected`.
- `App\Domain\Moderation\Enums\FlagReason`: `spam`, `offensive`, `not_a_review`, `conflict_of_interest`, `other`.

Unit test each enum's `label()` and any predicate methods.

Commit: `Add domain enums`

### 2.4 Rating value object
`App\Domain\Reviews\ValueObjects\Rating`
- `final readonly class` with `public int $value`.
- Private constructor; `public static function from(int $value): self`.
- Throws `InvalidRatingException::outOfRange()` outside 1–5.
- `equals(self $other): bool`.
- Implements `JsonSerializable` returning the int.
- Constants `MIN = 1`, `MAX = 5`.

Tests: accepts 1 and 5, rejects 0 and 6, equality, JSON serialisation.

Commit: `Add Rating value object`

### 2.5 CourtScores value object
`App\Domain\Reviews\ValueObjects\CourtScores`
- Holds four `Rating` properties: `glass`, `lighting`, `turf`, `facilities`.
- `public static function fromInts(int $glass, int $lighting, int $turf, int $facilities): self`.
- `overall(): float` — arithmetic mean rounded to one decimal.
- `toArray(): array{glass: int, lighting: int, turf: int, facilities: int}` with a PHPStan array-shape annotation.
- Implements `JsonSerializable`.

Tests: overall calculation, rounding, toArray shape.

Commit: `Add CourtScores value object`

### 2.6 AggregateScore value object
`App\Domain\Reviews\ValueObjects\AggregateScore`
- `public float $value` (0.0–5.0, one decimal), `public int $reviewCount` (>= 0).
- `public static function empty(): self` → 0.0 with count 0.
- `public static function of(float $value, int $reviewCount): self` with validation.
- `hasReviews(): bool`.

Tests: validation of both fields, `empty()`.

Commit: `Add AggregateScore value object`

### 2.7 Coordinates value object
`App\Domain\Venues\ValueObjects\Coordinates`
- `public float $latitude` (-90..90), `public float $longitude` (-180..180).
- Throws `App\Domain\Venues\Exceptions\InvalidCoordinatesException` on out-of-range values.
- `distanceToInKm(self $other): float` using the haversine formula (used for tests and display; Postgres does the real querying).

Tests: boundaries, a known distance (e.g. Manchester to London ≈ 262 km, allow ±2 km).

Commit: `Add Coordinates value object`

### 2.8 RatingAggregator contract and simple implementation
- `App\Domain\Reviews\Contracts\RatingAggregator` interface with one method:
  `public function aggregate(iterable $scores): AggregateScore;` where each item is a `CourtScores`.
- `App\Domain\Reviews\Aggregators\SimpleAverageAggregator` — mean of each `CourtScores::overall()`, rounded to one decimal; returns `AggregateScore::empty()` for an empty iterable.
- Bind the interface to `SimpleAverageAggregator` in a new `App\Providers\DomainServiceProvider` (register it in `bootstrap/providers.php`).

Tests: empty input, single review, several reviews, rounding.

Commit: `Add RatingAggregator contract with simple-average implementation`

### 2.9 Eloquent casts (framework-coupled, but belongs here)
- `App\Domain\Reviews\Casts\RatingCast` implementing `CastsAttributes` — int column ⇄ `Rating`.
- `App\Domain\Venues\Casts\CoordinatesCast` — reads from `latitude`/`longitude` columns into a `Coordinates` object and writes back to both. Note this cast targets two columns; document how `get`/`set` handle that.

Tests for casts can wait until models exist in Phase 3; add a TODO in the test folder.

Commit: `Add Eloquent casts for Rating and Coordinates`

## Acceptance criteria

- [ ] All value objects are `final readonly` and validate in construction.
- [ ] No value object depends on Laravel (casts and the provider are the only framework-coupled classes in this phase).
- [ ] Every enum, value object and the aggregator has unit tests with boundary cases.
- [ ] `RatingAggregator` is resolvable from the container and returns `SimpleAverageAggregator`.
- [ ] `composer check` and `npm run check` pass.
