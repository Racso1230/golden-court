<?php

declare(strict_types=1);

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Queries\CourtDimensionAveragesQuery;

it('averages each dimension over published reviews only', function (): void {
    $court = Court::factory()->create();
    Review::factory()->for($court)->create(['glass_rating' => 5, 'lighting_rating' => 4, 'turf_rating' => 3, 'facilities_rating' => 2]);
    Review::factory()->for($court)->create(['glass_rating' => 4, 'lighting_rating' => 4, 'turf_rating' => 4, 'facilities_rating' => 5]);
    Review::factory()->for($court)->pending()->create(['glass_rating' => 1, 'lighting_rating' => 1, 'turf_rating' => 1, 'facilities_rating' => 1]);

    $averages = (new CourtDimensionAveragesQuery($court))->get();

    expect($averages->glass)->toBe(4.5)
        ->and($averages->lighting)->toBe(4.0)
        ->and($averages->turf)->toBe(3.5)
        ->and($averages->facilities)->toBe(3.5);
});

it('is empty when the court has no published reviews', function (): void {
    $court = Court::factory()->create();
    Review::factory()->for($court)->pending()->create();

    $averages = (new CourtDimensionAveragesQuery($court))->get();

    expect($averages->glass)->toBeNull()
        ->and($averages->facilities)->toBeNull();
});
