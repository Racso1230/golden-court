<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Data;

use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * One of the signed-in user's own reviews, with where it was written and
 * its moderation status, for the account page.
 */
#[TypeScript]
final class OwnReviewData extends Data
{
    public function __construct(
        public ReviewData $review,
        public string $statusLabel,
        public bool $canEdit,
        public string $courtName,
        public string $courtSlug,
        public string $venueName,
        public string $venueSlug,
    ) {}

    /**
     * Expects `user`, `court.venue` and `reply.user` to be eager loaded.
     */
    public static function fromModel(Review $review, User $viewer): self
    {
        return new self(
            review: ReviewData::fromModel($review, $viewer),
            statusLabel: $review->status->label(),
            canEdit: $viewer->can('update', $review),
            courtName: $review->court->name,
            courtSlug: $review->court->slug,
            venueName: $review->court->venue->name,
            venueSlug: $review->court->venue->slug,
        );
    }
}
