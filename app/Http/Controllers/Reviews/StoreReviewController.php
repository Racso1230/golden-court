<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reviews;

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Actions\SubmitReviewAction;
use App\Domain\Reviews\Exceptions\ReviewAlreadyExistsException;
use App\Domain\Reviews\Exceptions\ReviewContentRejectedException;
use App\Domain\Reviews\Models\Review;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reviews\SubmitReviewRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class StoreReviewController extends Controller
{
    public function __invoke(SubmitReviewRequest $request, SubmitReviewAction $action): RedirectResponse
    {
        $user = $request->user() ?? abort(401);
        $court = Court::query()->whereKey($request->integer('court_id'))->firstOrFail();

        Gate::authorize('create', [Review::class, $court]);

        try {
            $action->handle($user, $request->toData());
        } catch (ReviewAlreadyExistsException) {
            throw ValidationException::withMessages([
                'court_id' => __('You have already reviewed this court.'),
            ]);
        } catch (ReviewContentRejectedException $exception) {
            throw ValidationException::withMessages(['body' => $exception->getMessage()]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Thanks, your review has been submitted.')]);

        $court->loadMissing('venue');

        return to_route('courts.show', [$court->venue, $court]);
    }
}
