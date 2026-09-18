<?php

declare(strict_types=1);

use App\Domain\Reviews\Aggregators\BayesianAggregator;
use App\Domain\Reviews\Aggregators\SimpleAverageAggregator;
use App\Domain\Reviews\Contracts\SiteStatistics;
use App\Domain\Reviews\ValueObjects\AggregateScore;
use App\Domain\Reviews\ValueObjects\CourtScores;

function siteMean(float $mean): SiteStatistics
{
    return new class($mean) implements SiteStatistics
    {
        public function __construct(private readonly float $mean) {}

        public function meanOverallScore(): float
        {
            return $this->mean;
        }
    };
}

/**
 * @return list<CourtScores>
 */
function uniformReviews(int $count, int $rating): array
{
    return array_fill(0, $count, CourtScores::fromInts($rating, $rating, $rating, $rating));
}

it('returns the empty score for no reviews', function (): void {
    $aggregator = new BayesianAggregator(siteMean(3.7), confidence: 5);

    expect($aggregator->aggregate([])->equals(AggregateScore::empty()))->toBeTrue();
});

it('ranks one five-star review below twenty reviews averaging 4.6', function (): void {
    $aggregator = new BayesianAggregator(siteMean(3.5), confidence: 5);

    $lonely = $aggregator->aggregate(uniformReviews(1, 5));
    // 4.6 overall: twelve 5s and eight 4s.
    $established = $aggregator->aggregate([...uniformReviews(12, 5), ...uniformReviews(8, 4)]);

    // (5 * 3.5 + 5) / 6 = 3.75 -> 3.8 ; (5 * 3.5 + 92) / 25 = 4.38 -> 4.4
    expect($lonely->value)->toBe(3.8)
        ->and($established->value)->toBe(4.4)
        ->and($lonely->value)->toBeLessThan($established->value)
        ->and($established->reviewCount)->toBe(20);
});

it('equals the simple average when the confidence is zero', function (): void {
    $scores = [...uniformReviews(3, 5), ...uniformReviews(2, 2)];

    $bayesian = (new BayesianAggregator(siteMean(3.0), confidence: 0))->aggregate($scores);
    $simple = (new SimpleAverageAggregator)->aggregate($scores);

    expect($bayesian->equals($simple))->toBeTrue();
});

it('pulls towards the prior more strongly with higher confidence', function (): void {
    $scores = uniformReviews(2, 5);

    $loose = (new BayesianAggregator(siteMean(3.0), confidence: 1))->aggregate($scores);
    $strict = (new BayesianAggregator(siteMean(3.0), confidence: 20))->aggregate($scores);

    expect($loose->value)->toBeGreaterThan($strict->value)
        ->and($strict->value)->toBeGreaterThan(3.0);
});

it('rejects a negative confidence', function (): void {
    expect(fn () => new BayesianAggregator(siteMean(3.0), confidence: -1))->toThrow(InvalidArgumentException::class);
});
