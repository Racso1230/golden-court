<?php

declare(strict_types=1);

namespace App\Http\Controllers\Discovery;

use App\Domain\Courts\Queries\GoldenCourtQuery;
use App\Domain\Venues\Data\VenueDetailData;
use App\Domain\Venues\Models\Venue;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Relations\Relation;
use Inertia\Inertia;
use Inertia\Response;

class VenueShowController extends Controller
{
    public function __invoke(Venue $venue, GoldenCourtQuery $goldenCourts): Response
    {
        $venue->load([
            'courts' => fn (Relation $courts): Relation => $courts->orderBy('id'),
            'owner',
        ]);

        return Inertia::render('Venues/Show', [
            'venue' => VenueDetailData::fromModel($venue, $goldenCourts->forCity($venue->city)),
        ]);
    }
}
