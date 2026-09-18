<?php

declare(strict_types=1);

use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Statistics\CachedSiteStatistics;
use Illuminate\Support\Facades\DB;

it('averages the overall score of published reviews only', function (): void {
    Review::factory()->create(['glass_rating' => 5, 'lighting_rating' => 5, 'turf_rating' => 5, 'facilities_rating' => 5]);
    Review::factory()->create(['glass_rating' => 3, 'lighting_rating' => 3, 'turf_rating' => 3, 'facilities_rating' => 3]);
    Review::factory()->pending()->create(['glass_rating' => 1, 'lighting_rating' => 1, 'turf_rating' => 1, 'facilities_rating' => 1]);

    expect((new CachedSiteStatistics)->meanOverallScore())->toBe(4.0);
});

it('falls back to the neutral midpoint when there are no reviews', function (): void {
    expect((new CachedSiteStatistics)->meanOverallScore())->toBe(CachedSiteStatistics::DEFAULT_MEAN);
});

it('caches the mean until told to forget it', function (): void {
    Review::factory()->create(['glass_rating' => 4, 'lighting_rating' => 4, 'turf_rating' => 4, 'facilities_rating' => 4]);
    $statistics = new CachedSiteStatistics;

    expect($statistics->meanOverallScore())->toBe(4.0);

    Review::factory()->create(['glass_rating' => 2, 'lighting_rating' => 2, 'turf_rating' => 2, 'facilities_rating' => 2]);

    DB::enableQueryLog();
    expect($statistics->meanOverallScore())->toBe(4.0)
        ->and(DB::getQueryLog())->toHaveCount(0);

    CachedSiteStatistics::forget();

    expect($statistics->meanOverallScore())->toBe(3.0);
});
