<?php

declare(strict_types=1);

namespace App\Domain\Claims\Actions;

use App\Domain\Claims\Enums\ClaimStatus;
use App\Domain\Claims\Events\VenueClaimApproved;
use App\Domain\Claims\Events\VenueClaimRejected;
use App\Domain\Claims\Exceptions\InvalidClaimTransitionException;
use App\Domain\Claims\Exceptions\VenueAlreadyClaimedException;
use App\Domain\Claims\Models\VenueClaim;
use App\Domain\Reviews\Exceptions\ModerationNotPermittedException;
use App\Domain\Users\Enums\Role;
use App\Domain\Users\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * An admin decides a pending claim. Approval hands the venue to the claimant
 * and promotes them to venue owner, all or nothing.
 */
final class ReviewVenueClaimAction
{
    /**
     * @throws ModerationNotPermittedException when the actor is not an admin
     * @throws InvalidClaimTransitionException when the claim is not pending or the decision is not a decision
     * @throws VenueAlreadyClaimedException when the venue gained an owner in the meantime
     */
    public function handle(VenueClaim $claim, User $admin, ClaimStatus $decision, ?string $rejectionReason = null): VenueClaim
    {
        if (! $admin->canModerate()) {
            throw ModerationNotPermittedException::forActor($admin);
        }

        if (! $claim->status->canTransitionTo($decision)) {
            throw InvalidClaimTransitionException::between($claim->status, $decision);
        }

        return DB::transaction(function () use ($claim, $admin, $decision, $rejectionReason): VenueClaim {
            $claim->loadMissing(['venue', 'user']);

            if ($decision === ClaimStatus::Approved) {
                if ($claim->venue->isClaimed()) {
                    throw VenueAlreadyClaimedException::forVenue($claim->venue_id);
                }

                $claim->venue->forceFill(['claimed_by_user_id' => $claim->user_id])->save();

                if ($claim->user->role === Role::Player) {
                    $claim->user->forceFill(['role' => Role::VenueOwner])->save();
                }
            }

            $claim->forceFill([
                'status' => $decision,
                'reviewed_by_user_id' => $admin->id,
                'reviewed_at' => now(),
                'rejection_reason' => $decision === ClaimStatus::Rejected ? $rejectionReason : null,
            ])->save();

            DB::afterCommit(fn () => $decision === ClaimStatus::Approved
                ? VenueClaimApproved::dispatch($claim->id, $claim->venue_id, $claim->user_id)
                : VenueClaimRejected::dispatch($claim->id, $claim->venue_id, $claim->user_id, $rejectionReason));

            return $claim;
        });
    }
}
