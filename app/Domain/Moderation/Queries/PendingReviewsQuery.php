<?php

declare(strict_types=1);

namespace App\Domain\Moderation\Queries;

use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Reviews\Models\Review;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Reviews held for moderation before going live, oldest first.
 */
final class PendingReviewsQuery
{
    /**
     * @return LengthAwarePaginator<int, Review>
     */
    public function paginate(int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        return Review::query()
            ->where('status', ReviewStatus::Pending)
            ->with(['user', 'court.venue', 'reply.user', 'flags.user'])
            ->orderBy('created_at')
            ->orderBy('id')
            ->paginate($perPage, page: $page);
    }
}
