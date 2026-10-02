<?php

declare(strict_types=1);

namespace App\Http\Controllers\Discovery;

use App\Domain\Claims\Models\VenueClaim;
use App\Domain\Courts\Queries\GoldenCourtQuery;
use App\Domain\Venues\Actions\BuildVenueShowPageMeta;
use App\Domain\Venues\Data\VenueDetailData;
use App\Domain\Venues\Models\Venue;
use App\Http\Controllers\Controller;
use App\Support\Seo\HeadTagRenderer;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class VenueShowController extends Controller
{
    public function __invoke(
        Request $request,
        Venue $venue,
        GoldenCourtQuery $goldenCourts,
        BuildVenueShowPageMeta $meta,
        HeadTagRenderer $head,
    ): Response {
        $venue->load([
            'courts' => fn (Relation $courts): Relation => $courts->orderBy('id'),
            'owner',
        ]);

        $viewer = $request->user();
        $detail = VenueDetailData::fromModel($venue, $goldenCourts->forCity($venue->city));

        return Inertia::render('Venues/Show', [
            'venue' => $detail,
            'canClaim' => $viewer !== null && Gate::forUser($viewer)->allows('create', [VenueClaim::class, $venue]),
            'head' => $head->render($meta->handle($detail)),
        ]);
    }
}
