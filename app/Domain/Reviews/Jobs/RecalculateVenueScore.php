<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Jobs;

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\ValueObjects\AggregateScore;
use App\Domain\Venues\Models\Venue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * A venue's score is the review-count-weighted mean of its courts' scores,
 * so a court with thirty reviews counts thirty times more than one with one.
 */
final class RecalculateVenueScore implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public readonly int $venueId) {}

    public function uniqueId(): string
    {
        return (string) $this->venueId;
    }

    public function handle(): void
    {
        $venue = Venue::query()->find($this->venueId);

        if ($venue === null) {
            return;
        }

        $aggregate = self::rollUp($venue->courts()->get()->all());

        Venue::query()->whereKey($venue->id)->toBase()->update([
            'aggregate_score' => $aggregate->value,
            'review_count' => $aggregate->reviewCount,
        ]);
    }

    /**
     * @param  iterable<Court>  $courts
     */
    public static function rollUp(iterable $courts): AggregateScore
    {
        $weightedSum = 0.0;
        $reviewCount = 0;

        foreach ($courts as $court) {
            $weightedSum += $court->aggregate_score * $court->review_count;
            $reviewCount += $court->review_count;
        }

        if ($reviewCount === 0) {
            return AggregateScore::empty();
        }

        return AggregateScore::of(round($weightedSum / $reviewCount, 1), $reviewCount);
    }
}
