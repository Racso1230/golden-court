<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Listeners;

use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Reviews\Events\ReviewStatusChanged;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Notifications\ReviewOutcomeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Only the outcomes a player cares about are worth a notification: going
 * live and being taken down. Flagging is an internal state.
 */
final class NotifyAuthorOfReviewOutcome implements ShouldQueue
{
    public function handle(ReviewStatusChanged $event): void
    {
        if (! in_array($event->to, [ReviewStatus::Published, ReviewStatus::Removed], true)) {
            return;
        }

        $review = Review::query()->with(['user', 'court.venue'])->find($event->reviewId);

        if ($review === null) {
            return;
        }

        $review->user->notify(new ReviewOutcomeNotification(
            reviewId: $review->id,
            outcome: $event->to,
            courtName: $review->court->name,
            courtSlug: $review->court->slug,
            venueName: $review->court->venue->name,
            venueSlug: $review->court->venue->slug,
        ));
    }
}
