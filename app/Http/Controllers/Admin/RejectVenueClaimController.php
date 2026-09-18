<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Claims\Actions\ReviewVenueClaimAction;
use App\Domain\Claims\Enums\ClaimStatus;
use App\Domain\Claims\Exceptions\InvalidClaimTransitionException;
use App\Domain\Claims\Models\VenueClaim;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectVenueClaimRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class RejectVenueClaimController extends Controller
{
    public function __invoke(RejectVenueClaimRequest $request, VenueClaim $claim, ReviewVenueClaimAction $action): RedirectResponse
    {
        $admin = $request->user() ?? abort(401);

        Gate::authorize('review', $claim);

        try {
            $action->handle($claim, $admin, ClaimStatus::Rejected, $request->rejectionReason());
        } catch (InvalidClaimTransitionException $exception) {
            throw ValidationException::withMessages(['claim' => $exception->getMessage()]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Claim rejected.')]);

        return back();
    }
}
