<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Reviews\Aggregators\SimpleAverageAggregator;
use App\Domain\Reviews\Contracts\RatingAggregator;
use App\Domain\Reviews\Contracts\ReviewPublicationRule;
use App\Domain\Reviews\Events\ReviewDeleted;
use App\Domain\Reviews\Events\ReviewStatusChanged;
use App\Domain\Reviews\Events\ReviewSubmitted;
use App\Domain\Reviews\Events\ReviewUpdated;
use App\Domain\Reviews\Listeners\QueueScoreRecalculation;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Policies\ReviewPolicy;
use App\Domain\Reviews\PublicationRules\VerifiedUserAutoPublishRule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Binds domain contracts to their current implementations and wires the
 * domain's events, listeners and policies.
 */
class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RatingAggregator::class, SimpleAverageAggregator::class);
        $this->app->bind(ReviewPublicationRule::class, VerifiedUserAutoPublishRule::class);
    }

    public function boot(): void
    {
        Gate::policy(Review::class, ReviewPolicy::class);

        Event::listen([
            ReviewSubmitted::class,
            ReviewUpdated::class,
            ReviewDeleted::class,
            ReviewStatusChanged::class,
        ], QueueScoreRecalculation::class);
    }
}
