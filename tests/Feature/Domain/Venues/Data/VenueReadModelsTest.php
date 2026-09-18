<?php

declare(strict_types=1);

use App\Domain\Courts\Models\Court;
use App\Domain\Users\Models\User;
use App\Domain\Venues\Data\VenueDetailData;
use App\Domain\Venues\Data\VenueSummaryData;
use App\Domain\Venues\Models\Venue;

it('builds a summary with court count and no distance when the search was not geographic', function (): void {
    $venue = Venue::factory()->create(['name' => 'Harbourside Padel', 'city' => 'Bristol']);
    Court::factory()->count(2)->for($venue)->create();

    $loaded = Venue::query()->withCount('courts')->whereKey($venue->id)->firstOrFail();
    $data = VenueSummaryData::fromModel($loaded, hasGoldenCourt: true);

    expect($data->name)->toBe('Harbourside Padel')
        ->and($data->city)->toBe('Bristol')
        ->and($data->courtCount)->toBe(2)
        ->and($data->distanceKm)->toBeNull()
        ->and($data->hasGoldenCourt)->toBeTrue()
        ->and($data->reviewCount)->toBe(0)
        ->and($data->aggregateScore)->toBe(0.0);
});

it('rounds the distance to one decimal when present', function (): void {
    $venue = Venue::factory()->create();
    $loaded = Venue::query()->withCount('courts')->selectRaw('venues.*, 12.3456 AS distance_km')->whereKey($venue->id)->firstOrFail();

    expect(VenueSummaryData::fromModel($loaded)->distanceKm)->toBe(12.3);
});

it('builds the detail model with courts, owner and the golden court badge', function (): void {
    $owner = User::factory()->venueOwner()->create(['display_name' => 'club_owner']);
    $venue = Venue::factory()->claimedBy($owner)->create(['address_line_2' => null, 'website' => 'https://example.test']);
    $golden = Court::factory()->for($venue)->create(['name' => 'Court 1']);
    Court::factory()->for($venue)->create(['name' => 'Court 2']);

    // A freshly created model lacks database defaults such as aggregate_score.
    $venue->refresh()->load(['courts', 'owner']);
    $data = VenueDetailData::fromModel($venue, $golden);

    expect($data->ownerDisplayName)->toBe('club_owner')
        ->and($data->website)->toBe('https://example.test')
        ->and($data->courtCount)->toBe(2)
        ->and($data->courts)->toHaveCount(2)
        ->and($data->courts[0]->isGoldenCourt)->toBeTrue()
        ->and($data->courts[1]->isGoldenCourt)->toBeFalse()
        ->and($data->courts[0]->courtTypeLabel)->toBe($golden->court_type->label());
});

it('serialises for the frontend with camelCase keys and enum values', function (): void {
    $venue = Venue::factory()->create();
    Court::factory()->for($venue)->create();
    $venue->refresh()->load(['courts', 'owner']);

    $array = VenueDetailData::fromModel($venue)->toArray();

    expect($array)->toHaveKeys(['addressLine1', 'aggregateScore', 'ownerDisplayName', 'courts'])
        ->and($array['ownerDisplayName'])->toBeNull()
        ->and($array['courts'][0]['courtType'])->toBeString();
});
