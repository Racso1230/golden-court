<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reviews;

use App\Domain\Courts\Data\CourtSummaryData;
use App\Domain\Reviews\Data\ReviewData;
use App\Domain\Reviews\Models\Review;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EditReviewController extends Controller
{
    public function __invoke(Request $request, Review $review): Response
    {
        Gate::authorize('update', $review);

        $review->loadMissing(['court.venue', 'user', 'reply.user']);

        return Inertia::render('Reviews/Edit', [
            'review' => ReviewData::fromModel($review, $request->user()),
            'court' => CourtSummaryData::fromModel($review->court),
            'venueName' => $review->court->venue->name,
            'venueSlug' => $review->court->venue->slug,
        ]);
    }
}
