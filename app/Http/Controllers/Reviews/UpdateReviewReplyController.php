<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reviews;

use App\Domain\Reviews\Actions\UpdateReviewReplyAction;
use App\Domain\Reviews\Models\Review;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reviews\ReviewReplyRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class UpdateReviewReplyController extends Controller
{
    public function __invoke(ReviewReplyRequest $request, Review $review, UpdateReviewReplyAction $action): RedirectResponse
    {
        $reply = $review->reply ?? abort(404);

        Gate::authorize('update', $reply);

        $action->handle($reply, $request->toData());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Your reply has been updated.')]);

        return back();
    }
}
