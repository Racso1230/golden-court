<?php

declare(strict_types=1);

namespace App\Domain\Moderation\Queries;

use App\Domain\Claims\Enums\ClaimStatus;
use App\Domain\Claims\Models\VenueClaim;
use App\Domain\Moderation\Data\ModerationCountsData;
use App\Domain\Moderation\Models\ReviewFlag;
use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Reviews\Models\Review;

/**
 * The three numbers on the admin dashboard.
 */
final class ModerationCountsQuery
{
    public function get(): ModerationCountsData
    {
        return new ModerationCountsData(
            pendingClaims: VenueClaim::query()->where('status', ClaimStatus::Pending)->count(),
            flaggedReviews: ReviewFlag::query()
                ->whereNull('resolved_at')
                ->whereHas('review', fn ($review) => $review->whereIn('status', [ReviewStatus::Published, ReviewStatus::Flagged]))
                ->distinct('review_id')
                ->count('review_id'),
            pendingReviews: Review::query()->where('status', ReviewStatus::Pending)->count(),
        );
    }
}
