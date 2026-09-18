<?php

declare(strict_types=1);

namespace App\Domain\Moderation\Data;

use App\Domain\Moderation\Models\ReviewFlag;
use App\Domain\Reviews\Data\ReviewData;
use App\Domain\Reviews\Models\Review;
use Spatie\LaravelData\Data;

/**
 * A review as the moderation queue sees it: the public read model plus where
 * it lives and who has flagged it.
 */
final class ModerationReviewData extends Data
{
    /**
     * @param  array<int, ReviewFlagData>  $flags
     */
    public function __construct(
        public ReviewData $review,
        public string $statusLabel,
        public string $authorEmail,
        public string $courtName,
        public string $courtSlug,
        public string $venueName,
        public string $venueSlug,
        public array $flags,
        public int $unresolvedFlagCount,
    ) {}

    /**
     * Expects `user`, `court.venue` and `flags.user` to be eager loaded.
     */
    public static function fromModel(Review $review): self
    {
        $flags = $review->flags
            ->sortByDesc('created_at')
            ->map(fn (ReviewFlag $flag): ReviewFlagData => ReviewFlagData::fromModel($flag))
            ->values()
            ->all();

        return new self(
            review: ReviewData::fromModel($review),
            statusLabel: $review->status->label(),
            authorEmail: $review->user->email,
            courtName: $review->court->name,
            courtSlug: $review->court->slug,
            venueName: $review->court->venue->name,
            venueSlug: $review->court->venue->slug,
            flags: $flags,
            unresolvedFlagCount: $review->flags->whereNull('resolved_at')->count(),
        );
    }
}
