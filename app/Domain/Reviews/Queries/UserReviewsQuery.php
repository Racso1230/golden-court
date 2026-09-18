<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Queries;

use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Everything one user has written, in any status, newest first.
 */
final class UserReviewsQuery
{
    public function __construct(private readonly User $user) {}

    /**
     * @return LengthAwarePaginator<int, Review>
     */
    public function paginate(int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        return $this->user->reviews()
            ->with(['user', 'court.venue', 'reply.user'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage, page: $page);
    }
}
