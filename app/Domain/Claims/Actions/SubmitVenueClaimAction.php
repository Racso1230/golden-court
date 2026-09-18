<?php

declare(strict_types=1);

namespace App\Domain\Claims\Actions;

use App\Domain\Claims\Data\SubmitVenueClaimData;
use App\Domain\Claims\Enums\ClaimStatus;
use App\Domain\Claims\Exceptions\ClaimAlreadyPendingException;
use App\Domain\Claims\Exceptions\VenueAlreadyClaimedException;
use App\Domain\Claims\Models\VenueClaim;
use App\Domain\Users\Models\User;
use App\Domain\Venues\Models\Venue;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

final class SubmitVenueClaimAction
{
    /**
     * @throws VenueAlreadyClaimedException when the venue already has an owner
     * @throws ClaimAlreadyPendingException when someone else's claim is awaiting review
     */
    public function handle(User $user, Venue $venue, SubmitVenueClaimData $data): VenueClaim
    {
        if ($venue->isClaimed()) {
            throw VenueAlreadyClaimedException::forVenue($venue->id);
        }

        return DB::transaction(function () use ($user, $venue, $data): VenueClaim {
            $claim = new VenueClaim;
            $claim->fill([
                'venue_id' => $venue->id,
                'user_id' => $user->id,
                'evidence' => $data->evidence,
            ]);
            $claim->forceFill(['status' => ClaimStatus::Pending]);

            try {
                DB::transaction(fn () => $claim->save());
            } catch (UniqueConstraintViolationException $exception) {
                // The partial unique index on (venue_id) WHERE status = 'pending' is the guard.
                throw ClaimAlreadyPendingException::forVenue($venue->id, $exception);
            }

            return $claim;
        });
    }
}
