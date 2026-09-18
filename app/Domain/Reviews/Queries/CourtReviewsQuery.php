<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Queries;

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Enums\ReviewSort;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * The published reviews of one court, sorted and paginated, with everything
 * `ReviewData` needs eager loaded so rendering a page costs a fixed number
 * of queries.
 */
final class CourtReviewsQuery
{
    private const string OVERALL_SQL = '(glass_rating + lighting_rating + turf_rating + facilities_rating)';

    public function __construct(
        private readonly Court $court,
        private readonly ReviewSort $sort = ReviewSort::Recent,
        private readonly ?User $viewer = null,
    ) {}

    /**
     * @return LengthAwarePaginator<int, Review>
     */
    public function paginate(int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        return $this->toBuilder()->paginate($perPage, page: $page);
    }

    /**
     * @return Builder<Review>
     */
    public function toBuilder(): Builder
    {
        $viewerId = $this->viewer?->id;

        $query = Review::query()
            ->published()
            ->where('court_id', $this->court->id)
            ->with([
                'user',
                'reply.user',
                // Only the viewer's own vote is needed to show "you found this helpful".
                'votes' => fn (Relation $votes): Relation => $votes->where('user_id', $viewerId ?? 0),
            ]);

        match ($this->sort) {
            ReviewSort::Recent => $query->orderByDesc('created_at'),
            ReviewSort::Helpful => $query->orderByDesc('helpful_count')->orderByDesc('created_at'),
            ReviewSort::Highest => $query->orderByRaw(self::OVERALL_SQL.' DESC')->orderByDesc('created_at'),
            ReviewSort::Lowest => $query->orderByRaw(self::OVERALL_SQL.' ASC')->orderByDesc('created_at'),
        };

        return $query->orderBy('id');
    }
}
