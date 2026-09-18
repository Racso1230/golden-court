<?php

declare(strict_types=1);

namespace App\Http\Controllers\Discovery;

use App\Domain\Courts\Queries\GoldenCourtQuery;
use App\Domain\Reviews\Data\RecentReviewData;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Queries\RecentReviewsQuery;
use App\Domain\Venues\Data\VenueSummaryData;
use App\Domain\Venues\Models\Venue;
use App\Domain\Venues\Queries\TopVenuesQuery;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(TopVenuesQuery $topVenues, RecentReviewsQuery $recentReviews, GoldenCourtQuery $goldenCourts): Response
    {
        $venues = $topVenues->get();
        $golden = $goldenCourts->forCities($venues->map(fn (Venue $venue): string => $venue->city)->all());

        return Inertia::render('Home', [
            'topVenues' => $venues->map(fn (Venue $venue): VenueSummaryData => VenueSummaryData::fromModel(
                $venue,
                $golden->get(GoldenCourtQuery::cityKey($venue->city))?->venue_id === $venue->id,
            ))->values()->all(),
            'recentReviews' => $recentReviews->get()
                ->map(fn (Review $review): RecentReviewData => RecentReviewData::fromModel($review))
                ->values()
                ->all(),
        ]);
    }
}
