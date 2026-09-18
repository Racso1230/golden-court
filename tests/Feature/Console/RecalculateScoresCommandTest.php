<?php

declare(strict_types=1);

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Jobs\RecalculateCourtScore;
use App\Domain\Reviews\Models\Review;
use Illuminate\Support\Facades\Bus;

it('queues a recalculation for every court', function (): void {
    Bus::fake();
    $courts = Court::factory()->count(3)->create();

    runArtisan('golden-court:recalculate-scores')
        ->expectsOutputToContain('Queued recalculation for 3 courts')
        ->assertSuccessful();

    foreach ($courts as $court) {
        Bus::assertDispatched(RecalculateCourtScore::class, fn (RecalculateCourtScore $job): bool => $job->courtId === $court->id);
    }
});

it('changes every score when the strategy is switched and the command is run synchronously', function (): void {
    config()->set('queue.default', 'sync');
    $court = Court::factory()->create();
    Review::factory()->for($court)->create(['glass_rating' => 5, 'lighting_rating' => 5, 'turf_rating' => 5, 'facilities_rating' => 5]);
    // A second court gives the site a mean below 5 so the prior has an effect.
    Review::factory()->for(Court::factory())->create(['glass_rating' => 2, 'lighting_rating' => 2, 'turf_rating' => 2, 'facilities_rating' => 2]);

    config()->set('golden_court.aggregation.strategy', 'simple');
    runArtisan('golden-court:recalculate-scores', ['--sync' => true])->assertSuccessful();
    expect($court->refresh()->aggregate_score)->toBe(5.0);

    config()->set('golden_court.aggregation.strategy', 'bayesian');
    config()->set('golden_court.aggregation.bayesian_confidence', 5);
    runArtisan('golden-court:recalculate-scores', ['--sync' => true])
        ->expectsOutputToContain('"bayesian" strategy')
        ->assertSuccessful();

    // Site mean is 3.5: (5 * 3.5 + 5) / 6 = 3.75 -> 3.8
    expect($court->refresh()->aggregate_score)->toBe(3.8)
        ->and($court->venue->refresh()->aggregate_score)->toBe(3.8);
});
