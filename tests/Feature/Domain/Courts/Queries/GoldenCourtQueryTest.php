<?php

declare(strict_types=1);

use App\Domain\Courts\Models\Court;
use App\Domain\Courts\Queries\GoldenCourtQuery;
use App\Domain\Venues\Models\Venue;
use Illuminate\Support\Facades\DB;

function scoredCourt(Venue $venue, float $score, int $reviews): Court
{
    $court = Court::factory()->for($venue)->create();
    Court::query()->whereKey($court->id)->toBase()->update(['aggregate_score' => $score, 'review_count' => $reviews]);

    return $court->refresh();
}

it('picks the highest scoring court in the city with at least five reviews', function (): void {
    $venue = Venue::factory()->create(['city' => 'Manchester']);
    scoredCourt($venue, 4.9, 2);
    $golden = scoredCourt($venue, 4.5, 8);
    scoredCourt($venue, 4.1, 20);

    expect((new GoldenCourtQuery)->forCity('Manchester')?->is($golden))->toBeTrue();
});

it('breaks a tie on review count', function (): void {
    $venue = Venue::factory()->create(['city' => 'Leeds']);
    scoredCourt($venue, 4.5, 6);
    $golden = scoredCourt($venue, 4.5, 12);

    expect((new GoldenCourtQuery)->forCity('Leeds')?->is($golden))->toBeTrue();
});

it('returns null when no court in the city qualifies', function (): void {
    scoredCourt(Venue::factory()->create(['city' => 'Bristol']), 5.0, 4);

    expect((new GoldenCourtQuery)->forCity('Bristol'))->toBeNull();
});

it('matches the city regardless of case and looks across venues', function (): void {
    scoredCourt(Venue::factory()->create(['city' => 'Glasgow']), 4.0, 10);
    $golden = scoredCourt(Venue::factory()->create(['city' => 'glasgow']), 4.7, 10);

    expect((new GoldenCourtQuery)->forCity('GLASGOW')?->is($golden))->toBeTrue();
});

it('resolves many cities in a single query and keys them by normalised city', function (): void {
    $manchester = scoredCourt(Venue::factory()->create(['city' => 'Manchester']), 4.5, 10);
    $leeds = scoredCourt(Venue::factory()->create(['city' => 'Leeds']), 4.2, 10);
    scoredCourt(Venue::factory()->create(['city' => 'Bristol']), 4.9, 1);

    DB::enableQueryLog();
    $result = (new GoldenCourtQuery)->forCities(['Manchester', 'leeds', 'Bristol', 'Manchester']);

    expect(DB::getQueryLog())->toHaveCount(1)
        ->and($result->keys()->all())->toEqualCanonicalizing(['manchester', 'leeds'])
        ->and($result->get('manchester')?->is($manchester))->toBeTrue()
        ->and($result->get('leeds')?->is($leeds))->toBeTrue();
});

it('caches per city, including cities with no golden court', function (): void {
    $venue = Venue::factory()->create(['city' => 'Cardiff']);
    scoredCourt($venue, 4.0, 10);
    Venue::factory()->create(['city' => 'Oxford']);
    $query = new GoldenCourtQuery;

    $query->forCities(['Cardiff', 'Oxford']);

    DB::enableQueryLog();
    $query->forCity('Cardiff');
    $query->forCity('Oxford');

    expect(DB::getQueryLog())->toHaveCount(0);
});

it('can be told to forget a city', function (): void {
    $venue = Venue::factory()->create(['city' => 'Cardiff']);
    $first = scoredCourt($venue, 4.0, 10);
    $query = new GoldenCourtQuery;

    expect($query->forCity('Cardiff')?->is($first))->toBeTrue();

    $better = scoredCourt($venue, 4.8, 10);

    expect($query->forCity('Cardiff')?->is($first))->toBeTrue();

    GoldenCourtQuery::forget('cardiff');

    expect($query->forCity('Cardiff')?->is($better))->toBeTrue();
});

it('ignores courts of soft-deleted venues', function (): void {
    $venue = Venue::factory()->create(['city' => 'Brighton']);
    scoredCourt($venue, 4.9, 10);
    $venue->delete();

    expect((new GoldenCourtQuery)->forCity('Brighton'))->toBeNull();
});
