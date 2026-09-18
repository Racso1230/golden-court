<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Domain\Claims\Data\VenueClaimData;
use App\Domain\Claims\Models\VenueClaim;
use App\Domain\Claims\Queries\UserClaimsQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClaimIndexController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user() ?? abort(401);

        return Inertia::render('Account/Claims', [
            'claims' => (new UserClaimsQuery($user))->get()
                ->map(fn (VenueClaim $claim): VenueClaimData => VenueClaimData::fromModel($claim))
                ->values()
                ->all(),
        ]);
    }
}
