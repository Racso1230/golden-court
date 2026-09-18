<?php

declare(strict_types=1);

namespace App\Domain\Claims\Listeners;

use App\Domain\Claims\Events\VenueClaimApproved;
use App\Domain\Claims\Events\VenueClaimRejected;
use App\Domain\Claims\Models\VenueClaim;
use App\Domain\Claims\Notifications\VenueClaimApprovedNotification;
use App\Domain\Claims\Notifications\VenueClaimRejectedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

final class NotifyClaimantOfDecision implements ShouldQueue
{
    public function handle(VenueClaimApproved|VenueClaimRejected $event): void
    {
        $claim = VenueClaim::query()->with(['venue', 'user'])->find($event->claimId);

        if ($claim === null) {
            return;
        }

        $claim->user->notify($event instanceof VenueClaimApproved
            ? new VenueClaimApprovedNotification($claim->venue->id, $claim->venue->name, $claim->venue->slug)
            : new VenueClaimRejectedNotification($claim->venue->id, $claim->venue->name, $event->rejectionReason));
    }
}
