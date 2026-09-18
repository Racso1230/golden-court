<?php

declare(strict_types=1);

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Jobs\RecalculateVenueScore;
use App\Domain\Venues\Models\Venue;

function courtWithScore(Venue $venue, float $score, int $count): Court
{
    $court = Court::factory()->for($venue)->create();
    Court::query()->whereKey($court->id)->toBase()->update(['aggregate_score' => $score, 'review_count' => $count]);

    return $court;
}

it('weights each court by its review count', function (): void {
    $venue = Venue::factory()->create();
    courtWithScore($venue, 5.0, 1);
    courtWithScore($venue, 3.0, 3);

    RecalculateVenueScore::dispatchSync($venue->id);

    $venue->refresh();

    // (5.0 * 1 + 3.0 * 3) / 4 = 3.5
    expect($venue->aggregate_score)->toBe(3.5)
        ->and($venue->review_count)->toBe(4);
});

it('ignores courts without reviews', function (): void {
    $venue = Venue::factory()->create();
    courtWithScore($venue, 4.2, 5);
    courtWithScore($venue, 0.0, 0);

    RecalculateVenueScore::dispatchSync($venue->id);

    $venue->refresh();

    expect($venue->aggregate_score)->toBe(4.2)
        ->and($venue->review_count)->toBe(5);
});

it('is empty when no court has reviews', function (): void {
    $venue = Venue::factory()->create();
    Venue::query()->whereKey($venue->id)->toBase()->update(['aggregate_score' => 3.3, 'review_count' => 9]);
    courtWithScore($venue, 0.0, 0);

    RecalculateVenueScore::dispatchSync($venue->id);

    $venue->refresh();

    expect($venue->aggregate_score)->toBe(0.0)
        ->and($venue->review_count)->toBe(0);
});

it('rounds the weighted mean to one decimal', function (): void {
    $venue = Venue::factory()->create();
    courtWithScore($venue, 4.0, 2);
    courtWithScore($venue, 3.0, 1);

    RecalculateVenueScore::dispatchSync($venue->id);

    // (4.0 * 2 + 3.0 * 1) / 3 = 3.666... -> 3.7
    expect($venue->refresh()->aggregate_score)->toBe(3.7);
});

it('does nothing for a venue that no longer exists', function (): void {
    RecalculateVenueScore::dispatchSync(999_999);

    expect(Venue::query()->count())->toBe(0);
});
