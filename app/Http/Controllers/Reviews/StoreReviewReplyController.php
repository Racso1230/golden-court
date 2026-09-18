<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reviews;

use App\Domain\Reviews\Actions\ReplyToReviewAction;
use App\Domain\Reviews\Exceptions\ReplyAlreadyExistsException;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Models\ReviewReply;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reviews\ReviewReplyRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class StoreReviewReplyController extends Controller
{
    public function __invoke(ReviewReplyRequest $request, Review $review, ReplyToReviewAction $action): RedirectResponse
    {
        $user = $request->user() ?? abort(401);

        Gate::authorize('create', [ReviewReply::class, $review]);

        try {
            $action->handle($user, $review, $request->toData());
        } catch (ReplyAlreadyExistsException) {
            throw ValidationException::withMessages(['body' => __('This review already has a reply.')]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Your reply has been posted.')]);

        return back();
    }
}
