<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Claims\Events\VenueClaimApproved;
use App\Domain\Claims\Events\VenueClaimRejected;
use App\Domain\Claims\Listeners\NotifyClaimantOfDecision;
use App\Domain\Claims\Models\VenueClaim;
use App\Domain\Claims\Policies\VenueClaimPolicy;
use App\Domain\Reviews\Aggregators\BayesianAggregator;
use App\Domain\Reviews\Aggregators\SimpleAverageAggregator;
use App\Domain\Reviews\ContentRules\BasicHeuristicsRule;
use App\Domain\Reviews\Contracts\RatingAggregator;
use App\Domain\Reviews\Contracts\ReviewContentRule;
use App\Domain\Reviews\Contracts\ReviewPublicationRule;
use App\Domain\Reviews\Contracts\SiteStatistics;
use App\Domain\Reviews\Enums\AggregationStrategy;
use App\Domain\Reviews\Events\ReviewDeleted;
use App\Domain\Reviews\Events\ReviewReplied;
use App\Domain\Reviews\Events\ReviewStatusChanged;
use App\Domain\Reviews\Events\ReviewSubmitted;
use App\Domain\Reviews\Events\ReviewUpdated;
use App\Domain\Reviews\Listeners\NotifyAuthorOfReply;
use App\Domain\Reviews\Listeners\NotifyAuthorOfReviewOutcome;
use App\Domain\Reviews\Listeners\QueueScoreRecalculation;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Models\ReviewReply;
use App\Domain\Reviews\Policies\ReviewPolicy;
use App\Domain\Reviews\Policies\ReviewReplyPolicy;
use App\Domain\Reviews\PublicationRules\VerifiedUserAutoPublishRule;
use App\Domain\Reviews\Statistics\CachedSiteStatistics;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
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
        $this->app->bind(RatingAggregator::class, function (Application $app): RatingAggregator {
            /** @var Repository $config */
            $config = $app->make(Repository::class);
            $strategy = AggregationStrategy::from($config->string('golden_court.aggregation.strategy'));

            return match ($strategy) {
                AggregationStrategy::Simple => new SimpleAverageAggregator,
                AggregationStrategy::Bayesian => new BayesianAggregator(
                    $app->make(SiteStatistics::class),
                    $config->integer('golden_court.aggregation.bayesian_confidence'),
                ),
            };
        });

        $this->app->singleton(SiteStatistics::class, CachedSiteStatistics::class);
        $this->app->bind(ReviewPublicationRule::class, VerifiedUserAutoPublishRule::class);
        $this->app->bind(ReviewContentRule::class, BasicHeuristicsRule::class);
    }

    public function boot(): void
    {
        Gate::policy(Review::class, ReviewPolicy::class);
        Gate::policy(ReviewReply::class, ReviewReplyPolicy::class);
        Gate::policy(VenueClaim::class, VenueClaimPolicy::class);

        Event::listen([
            ReviewSubmitted::class,
            ReviewUpdated::class,
            ReviewDeleted::class,
            ReviewStatusChanged::class,
        ], QueueScoreRecalculation::class);

        Event::listen(ReviewStatusChanged::class, NotifyAuthorOfReviewOutcome::class);
        Event::listen(ReviewReplied::class, NotifyAuthorOfReply::class);
        Event::listen([VenueClaimApproved::class, VenueClaimRejected::class], NotifyClaimantOfDecision::class);
    }
}
