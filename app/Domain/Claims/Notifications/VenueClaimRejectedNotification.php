<?php

declare(strict_types=1);

namespace App\Domain\Claims\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class VenueClaimRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly int $venueId,
        public readonly string $venueName,
        public readonly ?string $rejectionReason,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, int|string|null>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'venue_id' => $this->venueId,
            'venue_name' => $this->venueName,
            'rejection_reason' => $this->rejectionReason,
            'message' => $this->rejectionReason === null
                ? sprintf('Your claim on %s was not approved.', $this->venueName)
                : sprintf('Your claim on %s was not approved: %s', $this->venueName, $this->rejectionReason),
        ];
    }
}
