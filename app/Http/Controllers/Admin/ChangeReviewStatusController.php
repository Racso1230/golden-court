<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Reviews\Actions\ChangeReviewStatusAction;
use App\Domain\Reviews\Exceptions\InvalidStatusTransitionException;
use App\Domain\Reviews\Models\Review;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewOutcomeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ChangeReviewStatusController extends Controller
{
    public function __invoke(ReviewOutcomeRequest $request, Review $review, ChangeReviewStatusAction $action): RedirectResponse
    {
        $admin = $request->user() ?? abort(401);

        Gate::authorize('changeStatus', $review);

        try {
            $action->handle($review, $request->outcome(), $admin);
        } catch (InvalidStatusTransitionException $exception) {
            throw ValidationException::withMessages(['outcome' => $exception->getMessage()]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Review is now :status.', ['status' => $request->outcome()->label()])]);

        return back();
    }
}
