<?php

declare(strict_types=1);

namespace App\Domain\Claims\Policies;

use App\Domain\Claims\Models\VenueClaim;
use App\Domain\Users\Models\User;
use App\Domain\Venues\Models\Venue;

final class VenueClaimPolicy
{
    /**
     * Anyone signed in may claim a venue nobody owns yet. The database still
     * enforces one pending claim per venue.
     */
    public function create(User $user, Venue $venue): bool
    {
        return ! $venue->isClaimed();
    }

    public function review(User $user, VenueClaim $claim): bool
    {
        return $user->isAdmin();
    }
}
