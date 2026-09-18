<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reviews;

use App\Domain\Reviews\Actions\UpdateReviewAction;
use App\Domain\Reviews\Exceptions\ReviewContentRejectedException;
use App\Domain\Reviews\Models\Review;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reviews\UpdateReviewRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class UpdateReviewController extends Controller
{
    public function __invoke(UpdateReviewRequest $request, Review $review, UpdateReviewAction $action): RedirectResponse
    {
        Gate::authorize('update', $review);

        try {
            $action->handle($review, $request->toData());
        } catch (ReviewContentRejectedException $exception) {
            throw ValidationException::withMessages(['body' => $exception->getMessage()]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Your review has been updated.')]);

        $review->loadMissing('court.venue');

        return to_route('courts.show', [$review->court->venue, $review->court]);
    }
}
