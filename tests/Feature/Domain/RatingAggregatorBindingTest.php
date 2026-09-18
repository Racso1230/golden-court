<?php

declare(strict_types=1);

use App\Domain\Reviews\Aggregators\BayesianAggregator;
use App\Domain\Reviews\Aggregators\SimpleAverageAggregator;
use App\Domain\Reviews\Contracts\RatingAggregator;
use App\Domain\Reviews\Contracts\SiteStatistics;
use App\Domain\Reviews\Statistics\CachedSiteStatistics;

it('resolves the simple-average aggregator when the strategy is simple', function (): void {
    config()->set('golden_court.aggregation.strategy', 'simple');

    expect(app(RatingAggregator::class))->toBeInstanceOf(SimpleAverageAggregator::class);
});

it('resolves the Bayesian aggregator when the strategy is bayesian', function (): void {
    config()->set('golden_court.aggregation.strategy', 'bayesian');

    expect(app(RatingAggregator::class))->toBeInstanceOf(BayesianAggregator::class);
});

it('refuses an unknown strategy loudly', function (): void {
    config()->set('golden_court.aggregation.strategy', 'magic');

    expect(fn () => app(RatingAggregator::class))->toThrow(ValueError::class);
});

it('binds the cached site statistics as the prior source', function (): void {
    expect(app(SiteStatistics::class))->toBeInstanceOf(CachedSiteStatistics::class);
});
