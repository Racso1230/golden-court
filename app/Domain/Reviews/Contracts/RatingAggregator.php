<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Contracts;

use App\Domain\Reviews\ValueObjects\AggregateScore;
use App\Domain\Reviews\ValueObjects\CourtScores;

/**
 * Turns the scores of a set of reviews into a single aggregate.
 *
 * Implementations decide the statistics (plain mean, Bayesian prior, ...)
 * so the rest of the application never depends on a particular formula.
 */
interface RatingAggregator
{
    /**
     * @param  iterable<CourtScores>  $scores
     */
    public function aggregate(iterable $scores): AggregateScore;
}
