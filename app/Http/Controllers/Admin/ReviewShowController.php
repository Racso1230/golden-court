<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Moderation\Data\ModerationLogData;
use App\Domain\Moderation\Data\ModerationReviewData;
use App\Domain\Moderation\Enums\ModerationSubject;
use App\Domain\Moderation\Models\ModerationLog;
use App\Domain\Moderation\Queries\ModerationLogQuery;
use App\Domain\Reviews\Models\Review;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class ReviewShowController extends Controller
{
    public function __invoke(Review $review, ModerationLogQuery $logs): Response
    {
        $review->load(['user', 'court.venue', 'reply.user', 'flags.user']);

        return Inertia::render('Admin/Reviews/Show', [
            'review' => ModerationReviewData::fromModel($review),
            'log' => $logs->forSubject(ModerationSubject::Review, $review->id)
                ->map(fn (ModerationLog $entry): ModerationLogData => ModerationLogData::fromModel($entry))
                ->values()
                ->all(),
        ]);
    }
}
