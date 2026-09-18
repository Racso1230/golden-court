<?php

declare(strict_types=1);

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Contracts\RatingAggregator;
use App\Domain\Reviews\Jobs\RecalculateCourtScore;
use App\Domain\Reviews\Jobs\RecalculateVenueScore;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\ValueObjects\AggregateScore;
use App\Domain\Reviews\ValueObjects\CourtScores;
use Illuminate\Support\Facades\Bus;

it('writes the simple average of published reviews to the court', function (): void {
    Bus::fake([RecalculateVenueScore::class]);
    $court = Court::factory()->create();
    Review::factory()->for($court)->create(['glass_rating' => 5, 'lighting_rating' => 5, 'turf_rating' => 5, 'facilities_rating' => 5]);
    Review::factory()->for($court)->create(['glass_rating' => 3, 'lighting_rating' => 3, 'turf_rating' => 3, 'facilities_rating' => 3]);

    RecalculateCourtScore::dispatchSync($court->id);

    $court->refresh();

    expect($court->aggregate_score)->toBe(4.0)
        ->and($court->review_count)->toBe(2);
});

it('ignores pending, flagged and removed reviews', function (): void {
    Bus::fake([RecalculateVenueScore::class]);
    $court = Court::factory()->create();
    Review::factory()->for($court)->create(['glass_rating' => 4, 'lighting_rating' => 4, 'turf_rating' => 4, 'facilities_rating' => 4]);
    Review::factory()->for($court)->pending()->create(['glass_rating' => 1, 'lighting_rating' => 1, 'turf_rating' => 1, 'facilities_rating' => 1]);
    Review::factory()->for($court)->flagged()->create(['glass_rating' => 1, 'lighting_rating' => 1, 'turf_rating' => 1, 'facilities_rating' => 1]);
    Review::factory()->for($court)->removed()->create(['glass_rating' => 1, 'lighting_rating' => 1, 'turf_rating' => 1, 'facilities_rating' => 1]);

    RecalculateCourtScore::dispatchSync($court->id);

    $court->refresh();

    expect($court->aggregate_score)->toBe(4.0)
        ->and($court->review_count)->toBe(1);
});

it('resets to empty when the last published review disappears', function (): void {
    Bus::fake([RecalculateVenueScore::class]);
    $court = Court::factory()->create();
    Court::query()->whereKey($court->id)->toBase()->update(['aggregate_score' => 4.5, 'review_count' => 3]);

    RecalculateCourtScore::dispatchSync($court->id);

    $court->refresh();

    expect($court->aggregate_score)->toBe(0.0)
        ->and($court->review_count)->toBe(0);
});

it('uses whatever RatingAggregator is bound in the container', function (): void {
    Bus::fake([RecalculateVenueScore::class]);
    app()->instance(RatingAggregator::class, new class implements RatingAggregator
    {
        /**
         * @param  iterable<CourtScores>  $scores
         */
        public function aggregate(iterable $scores): AggregateScore
        {
            return AggregateScore::of(1.5, 99);
        }
    });
    $court = Court::factory()->create();
    Review::factory()->for($court)->create();

    RecalculateCourtScore::dispatchSync($court->id);

    $court->refresh();

    expect($court->aggregate_score)->toBe(1.5)
        ->and($court->review_count)->toBe(99);
});

it('rolls the change up to the venue', function (): void {
    Bus::fake([RecalculateVenueScore::class]);
    $court = Court::factory()->create();

    RecalculateCourtScore::dispatchSync($court->id);

    Bus::assertDispatched(RecalculateVenueScore::class, fn (RecalculateVenueScore $job): bool => $job->venueId === $court->venue_id);
});

it('does nothing for a court that no longer exists', function (): void {
    Bus::fake([RecalculateVenueScore::class]);

    RecalculateCourtScore::dispatchSync(999_999);

    Bus::assertNotDispatched(RecalculateVenueScore::class);
});

it('does not touch updated_at when recalculating', function (): void {
    Bus::fake([RecalculateVenueScore::class]);
    $court = Court::factory()->create(['updated_at' => '2026-01-01 12:00:00']);
    Review::factory()->for($court)->create();

    RecalculateCourtScore::dispatchSync($court->id);

    expect($court->refresh()->updated_at?->toDateTimeString())->toBe('2026-01-01 12:00:00');
});

it('is unique per court so a burst of changes queues one recalculation', function (): void {
    $job = new RecalculateCourtScore(42);

    expect($job->uniqueId())->toBe('42')
        ->and($job->tries)->toBe(3);
});
