<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reviews;

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Models\Review;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Renders the minimal review form. The full court page arrives in Phase 7.
 */
class CreateReviewController extends Controller
{
    public function __invoke(Court $court): Response
    {
        Gate::authorize('create', [Review::class, $court]);

        $court->loadMissing('venue');

        return Inertia::render('Reviews/Create', [
            'court' => [
                'id' => $court->id,
                'name' => $court->name,
                'venue' => [
                    'name' => $court->venue->name,
                    'city' => $court->venue->city,
                ],
            ],
        ]);
    }
}
