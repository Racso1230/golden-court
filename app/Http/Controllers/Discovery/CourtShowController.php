<?php

declare(strict_types=1);

namespace App\Http\Controllers\Discovery;

use App\Domain\Courts\Data\CourtDetailData;
use App\Domain\Courts\Models\Court;
use App\Domain\Courts\Queries\GoldenCourtQuery;
use App\Domain\Moderation\Enums\FlagReason;
use App\Domain\Reviews\Data\ReviewData;
use App\Domain\Reviews\Enums\ReviewSort;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Queries\CourtDimensionAveragesQuery;
use App\Domain\Reviews\Queries\CourtReviewsQuery;
use App\Domain\Shared\Data\OptionData;
use App\Domain\Venues\Models\Venue;
use App\Http\Controllers\Controller;
use App\Http\Requests\Discovery\CourtShowRequest;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CourtShowController extends Controller
{
    public function __invoke(CourtShowRequest $request, Venue $venue, Court $court, GoldenCourtQuery $goldenCourts): Response
    {
        $court->setRelation('venue', $venue);
        $viewer = $request->user();

        $reviews = (new CourtReviewsQuery($court, $request->sort(), $viewer))
            ->paginate(page: $request->page())
            ->withQueryString();
        $reviews->through(fn (Review $review): ReviewData => ReviewData::fromModel($review, $viewer));

        return Inertia::render('Courts/Show', [
            'court' => CourtDetailData::fromModel(
                $court,
                $goldenCourts->forCity($venue->city)?->is($court) ?? false,
                (new CourtDimensionAveragesQuery($court))->get(),
            ),
            'reviews' => $reviews,
            'sort' => $request->sort(),
            'sortOptions' => OptionData::fromEnum(ReviewSort::class),
            'flagReasons' => OptionData::fromEnum(FlagReason::class),
            'canReview' => $viewer !== null && Gate::forUser($viewer)->allows('create', [Review::class, $court]),
            'hasReviewed' => $viewer !== null && $viewer->reviews()->where('court_id', $court->id)->exists(),
            // UI hint only; ReviewReplyPolicy is what actually decides per review.
            'canReply' => $viewer !== null && ($viewer->isAdmin() || $venue->isOwnedBy($viewer)),
        ]);
    }
}
