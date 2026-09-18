<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Notifications;

use App\Domain\Reviews\Enums\ReviewStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Tells a player their review went live or was taken down.
 */
final class ReviewOutcomeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly int $reviewId,
        public readonly ReviewStatus $outcome,
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
            'outcome' => $this->outcome->value,
            'court_name' => $this->courtName,
            'court_slug' => $this->courtSlug,
            'venue_name' => $this->venueName,
            'venue_slug' => $this->venueSlug,
            'message' => $this->outcome === ReviewStatus::Published
                ? sprintf('Your review of %s at %s is now live.', $this->courtName, $this->venueName)
                : sprintf('Your review of %s at %s was removed by a moderator.', $this->courtName, $this->venueName),
        ];
    }
}
