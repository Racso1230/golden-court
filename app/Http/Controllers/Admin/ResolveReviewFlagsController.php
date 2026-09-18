<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Moderation\Actions\ResolveReviewFlagsAction;
use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Reviews\Exceptions\InvalidStatusTransitionException;
use App\Domain\Reviews\Models\Review;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewOutcomeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ResolveReviewFlagsController extends Controller
{
    public function __invoke(ReviewOutcomeRequest $request, Review $review, ResolveReviewFlagsAction $action): RedirectResponse
    {
        $admin = $request->user() ?? abort(401);

        Gate::authorize('changeStatus', $review);

        try {
            $action->handle($review, $admin, $request->outcome());
        } catch (InvalidStatusTransitionException $exception) {
            throw ValidationException::withMessages(['outcome' => $exception->getMessage()]);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $request->outcome() === ReviewStatus::Published
                ? __('Flags dismissed; the review stays live.')
                : __('Review removed and flags resolved.'),
        ]);

        return back();
    }
}
