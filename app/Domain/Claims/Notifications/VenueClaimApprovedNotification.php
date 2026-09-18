<?php

declare(strict_types=1);

namespace App\Domain\Claims\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class VenueClaimApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly int $venueId,
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
            'venue_id' => $this->venueId,
            'venue_name' => $this->venueName,
            'venue_slug' => $this->venueSlug,
            'message' => sprintf('Your claim on %s was approved. You can now reply to its reviews.', $this->venueName),
        ];
    }
}
