<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reviews;

use App\Domain\Reviews\Actions\DeleteReviewAction;
use App\Domain\Reviews\Models\Review;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class DestroyReviewController extends Controller
{
    public function __invoke(Review $review, DeleteReviewAction $action): RedirectResponse
    {
        Gate::authorize('delete', $review);

        $action->handle($review);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Your review has been deleted.')]);

        return to_route('dashboard');
    }
}
