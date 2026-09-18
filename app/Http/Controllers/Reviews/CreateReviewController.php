<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reviews;

use App\Domain\Courts\Data\CourtSummaryData;
use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Models\Review;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CreateReviewController extends Controller
{
    public function __invoke(Court $court): Response
    {
        Gate::authorize('create', [Review::class, $court]);

        $court->loadMissing('venue');

        return Inertia::render('Reviews/Create', [
            'court' => CourtSummaryData::fromModel($court),
            'venueName' => $court->venue->name,
            'venueSlug' => $court->venue->slug,
        ]);
    }
}
