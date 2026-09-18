<?php

declare(strict_types=1);

namespace App\Domain\Venues\Queries;

use App\Domain\Venues\Models\Venue;
use Illuminate\Database\Eloquent\Collection;

/**
 * The best-rated venues with enough reviews to mean something, for the home page.
 */
final class TopVenuesQuery
{
    public const int MIN_REVIEWS = 10;

    public function __construct(private readonly int $limit = 6) {}

    /**
     * @return Collection<int, Venue>
     */
    public function get(): Collection
    {
        return Venue::query()
            ->withCount('courts')
            ->where('review_count', '>=', self::MIN_REVIEWS)
            ->orderByDesc('aggregate_score')
            ->orderByDesc('review_count')
            ->orderBy('id')
            ->limit($this->limit)
            ->get();
    }
}
