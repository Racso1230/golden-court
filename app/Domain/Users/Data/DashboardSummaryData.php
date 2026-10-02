<?php

declare(strict_types=1);

namespace App\Domain\Users\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * The numbers on a signed-in user's dashboard.
 */
#[TypeScript]
final class DashboardSummaryData extends Data
{
    public function __construct(
        /** Reviews the user has written, in any status. */
        public int $reviewCount,
        /** Of those, the ones still waiting for moderation. */
        public int $pendingReviewCount,
        /** Helpful votes across the user's reviews. */
        public int $helpfulVoteCount,
        public int $claimCount,
        public int $pendingClaimCount,
        /** Venues the user owns after an approved claim. */
        public int $ownedVenueCount,
        /** Published reviews on the user's venues with no reply yet. */
        public int $unansweredReviewCount,
    ) {}
}
