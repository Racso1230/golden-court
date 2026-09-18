<?php

declare(strict_types=1);

use App\Domain\Courts\Enums\CourtType;
use App\Domain\Courts\Enums\Surface;
use App\Domain\Courts\Enums\WallType;
use App\Domain\Courts\Models\Court;
use App\Domain\Venues\Data\VenueSearchCriteria;
use App\Domain\Venues\Enums\VenueSort;
use App\Domain\Venues\Models\Venue;
use App\Domain\Venues\Queries\VenueSearchQuery;
use App\Domain\Venues\ValueObjects\Coordinates;

/**
 * @param  array<string, mixed>  $attributes
 */
function venueScored(array $attributes, float $score = 0.0, int $reviews = 0): Venue
{
    $venue = Venue::factory()->create($attributes);
    Venue::query()->whereKey($venue->id)->toBase()->update(['aggregate_score' => $score, 'review_count' => $reviews]);

    return $venue->refresh();
}

/**
 * @return array<int, string>
 */
function slugsFor(VenueSearchCriteria $criteria): array
{
    return (new VenueSearchQuery($criteria))->paginate()->getCollection()
        ->map(fn (Venue $venue): string => $venue->slug)
        ->all();
}

it('matches a term against venue names and cities via full-text search', function (): void {
    venueScored(['name' => 'Northern Quarter Padel', 'city' => 'Manchester', 'slug' => 'nq']);
    venueScored(['name' => 'Salford Quays Racket Centre', 'city' => 'Manchester', 'slug' => 'salford']);
    venueScored(['name' => 'Harbourside Padel', 'city' => 'Bristol', 'slug' => 'bristol']);

    expect(slugsFor(new VenueSearchCriteria(term: 'manchester')))->toEqualCanonicalizing(['nq', 'salford'])
        ->and(slugsFor(new VenueSearchCriteria(term: 'harbourside')))->toBe(['bristol'])
        ->and(slugsFor(new VenueSearchCriteria(term: 'Racket')))->toBe(['salford']);
});

it('filters by city regardless of case', function (): void {
    venueScored(['city' => 'Manchester', 'slug' => 'a']);
    venueScored(['city' => 'Leeds', 'slug' => 'b']);

    expect(slugsFor(new VenueSearchCriteria(city: 'MANCHESTER')))->toBe(['a']);
});

it('finds venues within a radius and reports their distance', function (): void {
    venueScored(['name' => 'Manchester Club', 'slug' => 'manchester', 'latitude' => 53.4808, 'longitude' => -2.2426]);
    venueScored(['name' => 'London Club', 'slug' => 'london', 'latitude' => 51.5074, 'longitude' => -0.1278]);

    $nearManchester = (new VenueSearchQuery(new VenueSearchCriteria(
        near: Coordinates::from(53.4808, -2.2426),
        radiusKm: 50,
    )))->paginate()->getCollection();

    expect($nearManchester->pluck('slug')->all())->toBe(['manchester'])
        ->and($nearManchester->first()?->distanceKm())->toBeLessThan(0.01);
});

it('orders by distance from the search point', function (): void {
    venueScored(['slug' => 'london', 'latitude' => 51.5074, 'longitude' => -0.1278], 5.0, 50);
    venueScored(['slug' => 'manchester', 'latitude' => 53.4808, 'longitude' => -2.2426], 1.0, 1);

    // From Birmingham: Manchester is ~113 km, London ~163 km.
    $results = (new VenueSearchQuery(new VenueSearchCriteria(
        near: Coordinates::from(52.4862, -1.8904),
        radiusKm: 200,
        sort: VenueSort::Distance,
    )))->paginate()->getCollection();

    expect($results->pluck('slug')->all())->toBe(['manchester', 'london'])
        ->and($results->first()?->distanceKm())->toBeGreaterThan(110.0)->toBeLessThan(116.0)
        ->and($results->last()?->distanceKm())->toBeGreaterThan(160.0)->toBeLessThan(166.0);
});

it('falls back to score ordering when distance is requested without a point', function (): void {
    venueScored(['slug' => 'low'], 2.0, 5);
    venueScored(['slug' => 'high'], 4.5, 5);

    expect(slugsFor(new VenueSearchCriteria(sort: VenueSort::Distance)))->toBe(['high', 'low']);
});

it('filters by court attributes through the venue', function (): void {
    $indoor = venueScored(['slug' => 'indoor']);
    Court::factory()->for($indoor)->create(['court_type' => CourtType::Indoor, 'wall_type' => WallType::Panoramic, 'surface' => Surface::Carpet]);
    $outdoor = venueScored(['slug' => 'outdoor']);
    Court::factory()->for($outdoor)->create(['court_type' => CourtType::Outdoor, 'wall_type' => WallType::Classic, 'surface' => Surface::ArtificialGrass]);

    expect(slugsFor(new VenueSearchCriteria(courtType: CourtType::Indoor)))->toBe(['indoor'])
        ->and(slugsFor(new VenueSearchCriteria(wallType: WallType::Classic)))->toBe(['outdoor'])
        ->and(slugsFor(new VenueSearchCriteria(surface: Surface::Carpet)))->toBe(['indoor'])
        ->and(slugsFor(new VenueSearchCriteria(courtType: CourtType::Indoor, surface: Surface::ArtificialGrass)))->toBe([]);
});

it('filters by minimum score', function (): void {
    venueScored(['slug' => 'great'], 4.6, 10);
    venueScored(['slug' => 'ok'], 3.2, 10);
    venueScored(['slug' => 'unrated']);

    expect(slugsFor(new VenueSearchCriteria(minScore: 4.0)))->toBe(['great'])
        ->and(slugsFor(new VenueSearchCriteria(minScore: 3.0)))->toBe(['great', 'ok']);
});

it('sorts by score, review count and name', function (): void {
    venueScored(['name' => 'Charlie', 'slug' => 'c'], 4.0, 30);
    venueScored(['name' => 'Alpha', 'slug' => 'a'], 4.8, 5);
    venueScored(['name' => 'Bravo', 'slug' => 'b'], 4.0, 50);

    expect(slugsFor(new VenueSearchCriteria(sort: VenueSort::Score)))->toBe(['a', 'b', 'c'])
        ->and(slugsFor(new VenueSearchCriteria(sort: VenueSort::Reviews)))->toBe(['b', 'c', 'a'])
        ->and(slugsFor(new VenueSearchCriteria(sort: VenueSort::Name)))->toBe(['a', 'b', 'c']);
});

it('composes several criteria without breaking pagination', function (): void {
    foreach (range(1, 25) as $i) {
        $venue = venueScored(['name' => sprintf('Manchester Padel %02d', $i), 'city' => 'Manchester', 'slug' => sprintf('m-%02d', $i)], 4.0, 10);
        Court::factory()->for($venue)->create(['court_type' => CourtType::Indoor]);
    }
    $excluded = venueScored(['name' => 'Manchester Outdoor', 'city' => 'Manchester', 'slug' => 'excluded'], 4.9, 10);
    Court::factory()->for($excluded)->create(['court_type' => CourtType::Outdoor]);
    venueScored(['name' => 'Manchester Low', 'city' => 'Manchester', 'slug' => 'low'], 1.0, 10);

    $criteria = fn (int $page): VenueSearchCriteria => new VenueSearchCriteria(
        term: 'manchester',
        courtType: CourtType::Indoor,
        minScore: 3.0,
        sort: VenueSort::Name,
        page: $page,
    );

    $pageOne = (new VenueSearchQuery($criteria(1)))->paginate();
    $pageTwo = (new VenueSearchQuery($criteria(2)))->paginate();

    expect($pageOne->total())->toBe(25)
        ->and($pageOne->count())->toBe(20)
        ->and($pageOne->lastPage())->toBe(2)
        ->and($pageTwo->count())->toBe(5)
        ->and($pageOne->getCollection()->first()?->slug)->toBe('m-01')
        ->and($pageTwo->getCollection()->last()?->slug)->toBe('m-25');
});

it('carries the court count on every result', function (): void {
    $venue = venueScored(['slug' => 'v']);
    Court::factory()->count(3)->for($venue)->create();

    expect((new VenueSearchQuery(new VenueSearchCriteria))->paginate()->getCollection()->first()?->courts_count)->toBe(3);
});

it('never lists soft-deleted venues', function (): void {
    venueScored(['slug' => 'live']);
    venueScored(['slug' => 'gone'])->delete();

    expect(slugsFor(new VenueSearchCriteria))->toBe(['live']);
});
