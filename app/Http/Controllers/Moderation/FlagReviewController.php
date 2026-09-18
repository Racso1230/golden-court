<?php

declare(strict_types=1);

namespace App\Http\Controllers\Moderation;

use App\Domain\Moderation\Actions\FlagReviewAction;
use App\Domain\Moderation\Exceptions\AlreadyFlaggedException;
use App\Domain\Reviews\Models\Review;
use App\Http\Controllers\Controller;
use App\Http\Requests\Moderation\FlagReviewRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class FlagReviewController extends Controller
{
    public function __invoke(FlagReviewRequest $request, Review $review, FlagReviewAction $action): RedirectResponse
    {
        $user = $request->user() ?? abort(401);

        Gate::authorize('flag', $review);

        try {
            $action->handle($user, $review, $request->toData());
        } catch (AlreadyFlaggedException) {
            throw ValidationException::withMessages(['reason' => __('You have already flagged this review.')]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Thanks, this review has been reported to our moderators.')]);

        return back();
    }
}
