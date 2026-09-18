<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Domain\Reviews\Data\OwnReviewData;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Queries\UserReviewsQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReviewIndexController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user() ?? abort(401);

        $reviews = (new UserReviewsQuery($user))
            ->paginate(page: max(1, $request->integer('page', 1)))
            ->withQueryString();
        $reviews->through(fn (Review $review): OwnReviewData => OwnReviewData::fromModel($review, $user));

        return Inertia::render('Account/Reviews', [
            'reviews' => $reviews,
        ]);
    }
}
