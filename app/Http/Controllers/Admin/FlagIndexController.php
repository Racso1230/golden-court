<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Moderation\Data\ModerationReviewData;
use App\Domain\Moderation\Queries\FlaggedReviewsQuery;
use App\Domain\Reviews\Models\Review;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FlagIndexController extends Controller
{
    public function __invoke(Request $request, FlaggedReviewsQuery $query): Response
    {
        $reviews = $query->paginate(page: max(1, $request->integer('page', 1)))->withQueryString();
        $reviews->through(fn (Review $review): ModerationReviewData => ModerationReviewData::fromModel($review));

        return Inertia::render('Admin/Flags/Index', [
            'reviews' => $reviews,
        ]);
    }
}
