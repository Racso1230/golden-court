<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reviews;

use App\Domain\Reviews\Actions\DeleteReviewReplyAction;
use App\Domain\Reviews\Models\Review;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class DestroyReviewReplyController extends Controller
{
    public function __invoke(Review $review, DeleteReviewReplyAction $action): RedirectResponse
    {
        $reply = $review->reply ?? abort(404);

        Gate::authorize('delete', $reply);

        $action->handle($reply);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Your reply has been deleted.')]);

        return back();
    }
}
