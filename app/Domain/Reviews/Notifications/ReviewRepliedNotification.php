<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class ReviewRepliedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly int $reviewId,
        public readonly int $replyId,
        public readonly string $courtName,
        public readonly string $courtSlug,
        public readonly string $venueName,
        public readonly string $venueSlug,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, int|string>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'review_id' => $this->reviewId,
            'reply_id' => $this->replyId,
            'court_name' => $this->courtName,
            'court_slug' => $this->courtSlug,
            'venue_name' => $this->venueName,
            'venue_slug' => $this->venueSlug,
            'message' => sprintf('%s replied to your review of %s.', $this->venueName, $this->courtName),
        ];
    }
}
