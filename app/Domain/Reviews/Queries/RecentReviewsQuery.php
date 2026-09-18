<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Queries;

use App\Domain\Reviews\Models\Review;
use Illuminate\Database\Eloquent\Collection;

/**
 * The latest published reviews across all courts, for the home page.
 */
final class RecentReviewsQuery
{
    public function __construct(private readonly int $limit = 6) {}

    /**
     * @return Collection<int, Review>
     */
    public function get(): Collection
    {
        return Review::query()
            ->published()
            ->with(['user', 'court.venue'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($this->limit)
            ->get();
    }
}
