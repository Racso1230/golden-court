<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Aggregators;

use App\Domain\Reviews\Contracts\RatingAggregator;
use App\Domain\Reviews\Contracts\SiteStatistics;
use App\Domain\Reviews\ValueObjects\AggregateScore;
use App\Domain\Reviews\ValueObjects\CourtScores;
use InvalidArgumentException;

/**
 * A Bayesian average: (C * m + sum of overalls) / (C + n), where m is the
 * site-wide mean and C says how many reviews it takes before a court's own
 * average outweighs that prior. With C = 0 it is the simple mean.
 */
final class BayesianAggregator implements RatingAggregator
{
    public function __construct(
        private readonly SiteStatistics $statistics,
        private readonly int $confidence,
    ) {
        if ($confidence < 0) {
            throw new InvalidArgumentException('Bayesian confidence cannot be negative.');
        }
    }

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

        $prior = $this->confidence * $this->statistics->meanOverallScore();
        $score = ($prior + $sum) / ($this->confidence + $count);

        return AggregateScore::of(round(min(AggregateScore::MAX, max(AggregateScore::MIN, $score)), 1), $count);
    }
}
