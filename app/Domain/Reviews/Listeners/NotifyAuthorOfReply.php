<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Listeners;

use App\Domain\Reviews\Events\ReviewReplied;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Notifications\ReviewRepliedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

final class NotifyAuthorOfReply implements ShouldQueue
{
    public function handle(ReviewReplied $event): void
    {
        $review = Review::query()->with(['user', 'court.venue'])->find($event->reviewId);

        if ($review === null) {
            return;
        }

        $review->user->notify(new ReviewRepliedNotification(
            reviewId: $review->id,
            replyId: $event->replyId,
            courtName: $review->court->name,
            courtSlug: $review->court->slug,
            venueName: $review->court->venue->name,
            venueSlug: $review->court->venue->slug,
        ));
    }
}
