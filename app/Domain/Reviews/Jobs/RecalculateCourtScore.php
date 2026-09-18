<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Jobs;

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Contracts\RatingAggregator;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\ValueObjects\CourtScores;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Recomputes a court's denormalised score from its published reviews, then
 * rolls the change up to the venue.
 */
final class RecalculateCourtScore implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public readonly int $courtId) {}

    public function uniqueId(): string
    {
        return (string) $this->courtId;
    }

    public function handle(RatingAggregator $aggregator): void
    {
        $court = Court::query()->find($this->courtId);

        if ($court === null) {
            return;
        }

        $scores = $court->publishedReviews()
            ->get()
            ->map(fn (Review $review): CourtScores => $review->scores())
            ->all();

        $aggregate = $aggregator->aggregate($scores);

        // A plain UPDATE: recalculating a score is not an edit, so updated_at is left alone.
        Court::query()->whereKey($court->id)->toBase()->update([
            'aggregate_score' => $aggregate->value,
            'review_count' => $aggregate->reviewCount,
        ]);

        RecalculateVenueScore::dispatch($court->venue_id);
    }
}
