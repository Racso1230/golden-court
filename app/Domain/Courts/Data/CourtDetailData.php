<?php

declare(strict_types=1);

namespace App\Domain\Courts\Data;

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Data\DimensionAveragesData;
use App\Domain\Reviews\Data\ReviewData;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\PaginatedDataCollection;

/**
 * A court page: the summary, its venue, per-dimension averages and a page of reviews.
 */
final class CourtDetailData extends Data
{
    /**
     * @param  PaginatedDataCollection<int, ReviewData>  $reviews
     */
    public function __construct(
        public CourtSummaryData $court,
        public int $venueId,
        public string $venueName,
        public string $venueSlug,
        public string $venueCity,
        public DimensionAveragesData $averages,
        #[DataCollectionOf(ReviewData::class)]
        public PaginatedDataCollection $reviews,
    ) {}

    /**
     * Expects `venue` to be eager loaded on the court.
     *
     * @param  LengthAwarePaginator<int, Review>  $reviews
     */
    public static function fromModel(
        Court $court,
        bool $isGoldenCourt,
        DimensionAveragesData $averages,
        LengthAwarePaginator $reviews,
        ?User $viewer = null,
    ): self {
        $reviews->through(fn (Review $review): ReviewData => ReviewData::fromModel($review, $viewer));

        /** @var PaginatedDataCollection<int, ReviewData> $collection */
        $collection = new PaginatedDataCollection(ReviewData::class, $reviews);

        return new self(
            court: CourtSummaryData::fromModel($court, $isGoldenCourt),
            venueId: $court->venue->id,
            venueName: $court->venue->name,
            venueSlug: $court->venue->slug,
            venueCity: $court->venue->city,
            averages: $averages,
            reviews: $collection,
        );
    }
}
