<?php

declare(strict_types=1);

namespace App\Domain\Users\Queries;

use App\Domain\Claims\Enums\ClaimStatus;
use App\Domain\Claims\Models\VenueClaim;
use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Data\DashboardSummaryData;
use App\Domain\Users\Models\User;
use App\Domain\Venues\Models\Venue;
use Illuminate\Database\Eloquent\Builder;

/**
 * Counts for the dashboard, in four aggregate queries whatever the
 * user's history.
 */
final readonly class DashboardSummaryQuery
{
    public function __construct(private User $user) {}

    public function get(): DashboardSummaryData
    {
        $reviews = Review::query()
            ->whereBelongsTo($this->user)
            ->toBase()
            ->selectRaw('count(*) as total')
            ->selectRaw('count(*) filter (where status = ?) as pending', [ReviewStatus::Pending->value])
            ->selectRaw('coalesce(sum(helpful_count), 0) as helpful')
            ->first();

        $claims = VenueClaim::query()
            ->where('user_id', $this->user->id)
            ->toBase()
            ->selectRaw('count(*) as total')
            ->selectRaw('count(*) filter (where status = ?) as pending', [ClaimStatus::Pending->value])
            ->first();

        $ownedVenues = Venue::query()->where('claimed_by_user_id', $this->user->id)->count();

        $unanswered = $ownedVenues === 0 ? 0 : Review::query()
            ->where('status', ReviewStatus::Published)
            ->whereDoesntHave('reply')
            ->whereHas('court', fn (Builder $court) => $court
                ->whereHas('venue', fn (Builder $venue) => $venue->where('claimed_by_user_id', $this->user->id)))
            ->count();

        return new DashboardSummaryData(
            reviewCount: (int) ($reviews->total ?? 0),
            pendingReviewCount: (int) ($reviews->pending ?? 0),
            helpfulVoteCount: (int) ($reviews->helpful ?? 0),
            claimCount: (int) ($claims->total ?? 0),
            pendingClaimCount: (int) ($claims->pending ?? 0),
            ownedVenueCount: $ownedVenues,
            unansweredReviewCount: $unanswered,
        );
    }
}
