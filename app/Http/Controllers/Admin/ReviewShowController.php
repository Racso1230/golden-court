<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Moderation\Data\ModerationReviewData;
use App\Domain\Reviews\Models\Review;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ReviewShowController extends Controller
{
    public function __invoke(Review $review): Response
    {
        $review->load(['user', 'court.venue', 'reply.user', 'flags.user']);

        return Inertia::render('Admin/Reviews/Show', [
            'review' => ModerationReviewData::fromModel($review),
        ]);
    }
}
