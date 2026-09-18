<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Aggregators;

use App\Domain\Reviews\Contracts\RatingAggregator;
use App\Domain\Reviews\ValueObjects\AggregateScore;
use App\Domain\Reviews\ValueObjects\CourtScores;

/**
 * The arithmetic mean of each review's overall score.
 */
final class SimpleAverageAggregator implements RatingAggregator
{
    /**
     * @param  iterable<CourtScores>  $scores
     */
    public function aggregate(iterable $scores): AggregateScore
    {
        $sum = 0.0;
        $count = 0;

        foreach ($scores as $courtScores) {
            $sum += $courtScores->overall();
            $count++;
        }

        if ($count === 0) {
            return AggregateScore::empty();
        }

        return AggregateScore::of(round($sum / $count, 1), $count);
    }
}
