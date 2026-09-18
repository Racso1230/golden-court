<?php

declare(strict_types=1);

namespace App\Http\Controllers\Claims;

use App\Domain\Claims\Actions\SubmitVenueClaimAction;
use App\Domain\Claims\Exceptions\ClaimAlreadyPendingException;
use App\Domain\Claims\Exceptions\VenueAlreadyClaimedException;
use App\Domain\Claims\Models\VenueClaim;
use App\Domain\Venues\Models\Venue;
use App\Http\Controllers\Controller;
use App\Http\Requests\Claims\SubmitVenueClaimRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class StoreVenueClaimController extends Controller
{
    public function __invoke(SubmitVenueClaimRequest $request, Venue $venue, SubmitVenueClaimAction $action): RedirectResponse
    {
        $user = $request->user() ?? abort(401);

        Gate::authorize('create', [VenueClaim::class, $venue]);

        try {
            $action->handle($user, $venue, $request->toData());
        } catch (VenueAlreadyClaimedException) {
            throw ValidationException::withMessages(['evidence' => __('This venue already has an owner.')]);
        } catch (ClaimAlreadyPendingException) {
            throw ValidationException::withMessages(['evidence' => __('A claim on this venue is already awaiting review.')]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Your claim has been submitted for review.')]);

        return to_route('venues.show', $venue);
    }
}
