<?php

declare(strict_types=1);

use App\Domain\Reviews\Aggregators\SimpleAverageAggregator;
use App\Domain\Reviews\Contracts\RatingAggregator;

it('resolves the RatingAggregator contract to the simple-average implementation', function (): void {
    expect(app(RatingAggregator::class))->toBeInstanceOf(SimpleAverageAggregator::class);
});
