<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reviews;

use App\Domain\Reviews\Actions\ToggleReviewVoteAction;
use App\Domain\Reviews\Models\Review;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ToggleReviewVoteController extends Controller
{
    public function __invoke(Request $request, Review $review, ToggleReviewVoteAction $action): RedirectResponse
    {
        $user = $request->user() ?? abort(401);

        Gate::authorize('vote', $review);

        $voted = $action->handle($user, $review);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $voted ? __('Thanks, marked as helpful.') : __('Your helpful vote was removed.'),
        ]);

        return back();
    }
}
