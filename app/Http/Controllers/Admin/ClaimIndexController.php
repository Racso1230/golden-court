<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Claims\Data\VenueClaimData;
use App\Domain\Claims\Models\VenueClaim;
use App\Domain\Claims\Queries\PendingClaimsQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClaimIndexController extends Controller
{
    public function __invoke(Request $request, PendingClaimsQuery $query): Response
    {
        $claims = $query->paginate(page: max(1, $request->integer('page', 1)))->withQueryString();
        $claims->through(fn (VenueClaim $claim): VenueClaimData => VenueClaimData::fromModel($claim));

        return Inertia::render('Admin/Claims/Index', [
            'claims' => $claims,
        ]);
    }
}
