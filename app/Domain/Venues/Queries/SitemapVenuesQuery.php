<?php

declare(strict_types=1);

namespace App\Domain\Venues\Queries;

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Venues\Models\Venue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Every visible venue with its visible courts, each court carrying the date
 * of its newest published review as `latest_review_at`. Soft-deleted venues
 * and courts are left out by their global scopes. Two queries in total.
 */
final class SitemapVenuesQuery
{
    /**
     * @return Collection<int, Venue>
     */
    public function get(): Collection
    {
        return Venue::query()
            ->select(['id', 'slug', 'updated_at'])
            ->with(['courts' => fn (Relation $courts): Relation => $courts
                ->select(['id', 'venue_id', 'slug', 'updated_at'])
                ->withMax(['reviews as latest_review_at' => fn (HasMany|Builder $reviews) => $reviews->where('status', ReviewStatus::Published)], 'created_at')
                ->orderBy('id')])
            ->orderBy('id')
            ->get();
    }

    public static function latestReviewAt(Court $court): ?string
    {
        // Selected by withMax above; read raw so strict mode never sees a missing attribute.
        $value = $court->getAttributes()['latest_review_at'] ?? null;

        return is_string($value) ? $value : null;
    }
}
