<?php

declare(strict_types=1);

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Data\ReviewData;
use App\Domain\Reviews\Enums\ReviewSort;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Models\ReviewVote;
use App\Domain\Reviews\Queries\CourtReviewsQuery;
use App\Domain\Users\Models\User;
use Illuminate\Support\Facades\DB;

function ratedReview(Court $court, int $rating, int $helpful = 0, ?string $createdAt = null): Review
{
    return Review::factory()->for($court)->create([
        'glass_rating' => $rating,
        'lighting_rating' => $rating,
        'turf_rating' => $rating,
        'facilities_rating' => $rating,
        'helpful_count' => $helpful,
        'created_at' => $createdAt ?? now(),
    ]);
}

it('lists only published reviews of the court', function (): void {
    $court = Court::factory()->create();
    $published = Review::factory()->for($court)->create();
    Review::factory()->for($court)->pending()->create();
    Review::factory()->for($court)->removed()->create();
    Review::factory()->create();

    $page = (new CourtReviewsQuery($court))->paginate();

    expect($page->total())->toBe(1)
        ->and($page->getCollection()->first()?->is($published))->toBeTrue();
});

it('sorts by recency, helpfulness and rating', function (): void {
    $court = Court::factory()->create();
    $old = ratedReview($court, 5, helpful: 1, createdAt: '2026-01-01 10:00:00');
    $mid = ratedReview($court, 2, helpful: 9, createdAt: '2026-05-01 10:00:00');
    $new = ratedReview($court, 4, helpful: 3, createdAt: '2026-09-01 10:00:00');

    $ids = fn (ReviewSort $sort): array => (new CourtReviewsQuery($court, $sort))->paginate()->getCollection()->pluck('id')->all();

    expect($ids(ReviewSort::Recent))->toBe([$new->id, $mid->id, $old->id])
        ->and($ids(ReviewSort::Helpful))->toBe([$mid->id, $new->id, $old->id])
        ->and($ids(ReviewSort::Highest))->toBe([$old->id, $new->id, $mid->id])
        ->and($ids(ReviewSort::Lowest))->toBe([$mid->id, $new->id, $old->id]);
});

it('paginates', function (): void {
    $court = Court::factory()->create();
    Review::factory()->count(12)->for($court)->create();

    $pageTwo = (new CourtReviewsQuery($court))->paginate(perPage: 10, page: 2);

    expect($pageTwo->total())->toBe(12)
        ->and($pageTwo->count())->toBe(2);
});

it('eager loads everything ReviewData needs, including the viewer vote', function (): void {
    $court = Court::factory()->create();
    Review::factory()->count(5)->for($court)->create();
    $voted = Review::factory()->for($court)->create();
    $viewer = User::factory()->player()->create();
    ReviewVote::factory()->for($voted)->for($viewer)->create();
    ReviewVote::factory()->for($voted)->create();

    DB::enableQueryLog();
    $reviews = (new CourtReviewsQuery($court, viewer: $viewer))->paginate()->getCollection();
    $data = $reviews->map(fn (Review $review): ReviewData => ReviewData::fromModel($review, $viewer));

    // count + reviews + users + replies + reply users + votes
    expect(count(DB::getQueryLog()))->toBeLessThanOrEqual(6)
        ->and($data->firstWhere('id', $voted->id)?->hasVoted)->toBeTrue()
        ->and($data->where('hasVoted', false))->toHaveCount(5);
});
