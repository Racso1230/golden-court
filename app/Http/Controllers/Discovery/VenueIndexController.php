<?php

declare(strict_types=1);

namespace App\Http\Controllers\Discovery;

use App\Domain\Courts\Enums\CourtType;
use App\Domain\Courts\Enums\Surface;
use App\Domain\Courts\Enums\WallType;
use App\Domain\Courts\Queries\GoldenCourtQuery;
use App\Domain\Shared\Data\OptionData;
use App\Domain\Venues\Data\VenueSummaryData;
use App\Domain\Venues\Enums\VenueSort;
use App\Domain\Venues\Models\Venue;
use App\Domain\Venues\Queries\VenueSearchQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Discovery\VenueSearchRequest;
use Inertia\Inertia;
use Inertia\Response;

class VenueIndexController extends Controller
{
    public function __invoke(VenueSearchRequest $request, GoldenCourtQuery $goldenCourts): Response
    {
        $criteria = $request->toCriteria();
        $venues = (new VenueSearchQuery($criteria))->paginate()->withQueryString();

        $golden = $goldenCourts->forCities(
            $venues->getCollection()->map(fn (Venue $venue): string => $venue->city)->all(),
        );

        $venues->through(fn (Venue $venue): VenueSummaryData => VenueSummaryData::fromModel(
            $venue,
            $golden->get(GoldenCourtQuery::cityKey($venue->city))?->venue_id === $venue->id,
        ));

        return Inertia::render('Venues/Index', [
            'venues' => $venues,
            'criteria' => $criteria,
            'options' => [
                'courtTypes' => OptionData::fromEnum(CourtType::class),
                'wallTypes' => OptionData::fromEnum(WallType::class),
                'surfaces' => OptionData::fromEnum(Surface::class),
                'sorts' => OptionData::fromEnum(VenueSort::class),
            ],
        ]);
    }
}
