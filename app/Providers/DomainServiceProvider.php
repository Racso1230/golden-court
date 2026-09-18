<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Reviews\Aggregators\SimpleAverageAggregator;
use App\Domain\Reviews\Contracts\RatingAggregator;
use Illuminate\Support\ServiceProvider;

/**
 * Binds domain contracts to their current implementations.
 */
class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RatingAggregator::class, SimpleAverageAggregator::class);
    }
}
