<?php

declare(strict_types=1);

use App\Domain\Reviews\Aggregators\SimpleAverageAggregator;
use App\Domain\Reviews\ValueObjects\AggregateScore;
use App\Domain\Reviews\ValueObjects\CourtScores;

it('returns the empty score for no reviews', function (): void {
    $aggregator = new SimpleAverageAggregator;

    expect($aggregator->aggregate([])->equals(AggregateScore::empty()))->toBeTrue();
});

it('returns the overall of a single review', function (): void {
    $aggregator = new SimpleAverageAggregator;

    $score = $aggregator->aggregate([CourtScores::fromInts(5, 4, 3, 2)]);

    expect($score->value)->toBe(3.5)
        ->and($score->reviewCount)->toBe(1);
});

it('averages the overall of several reviews', function (): void {
    $aggregator = new SimpleAverageAggregator;

    $score = $aggregator->aggregate([
        CourtScores::fromInts(5, 5, 5, 5), // 5.0
        CourtScores::fromInts(3, 3, 3, 3), // 3.0
        CourtScores::fromInts(4, 4, 4, 4), // 4.0
    ]);

    expect($score->value)->toBe(4.0)
        ->and($score->reviewCount)->toBe(3);
});

it('rounds the mean to one decimal place', function (): void {
    $aggregator = new SimpleAverageAggregator;

    // (5.0 + 4.0 + 4.0) / 3 = 4.333... -> 4.3
    $score = $aggregator->aggregate([
        CourtScores::fromInts(5, 5, 5, 5),
        CourtScores::fromInts(4, 4, 4, 4),
        CourtScores::fromInts(4, 4, 4, 4),
    ]);

    expect($score->value)->toBe(4.3);
});

it('accepts any iterable, not only arrays', function (): void {
    $aggregator = new SimpleAverageAggregator;

    $generator = (function (): Generator {
        yield CourtScores::fromInts(2, 2, 2, 2);
        yield CourtScores::fromInts(4, 4, 4, 4);
    })();

    $score = $aggregator->aggregate($generator);

    expect($score->value)->toBe(3.0)
        ->and($score->reviewCount)->toBe(2);
});
