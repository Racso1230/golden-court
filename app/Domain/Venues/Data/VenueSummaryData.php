<?php

declare(strict_types=1);

namespace App\Domain\Venues\Data;

use App\Domain\Venues\Models\Venue;
use Spatie\LaravelData\Data;

/**
 * A venue as it appears in search results and lists.
 */
final class VenueSummaryData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public string $city,
        public float $aggregateScore,
        public int $reviewCount,
        public int $courtCount,
        public ?float $distanceKm,
        public bool $hasGoldenCourt,
    ) {}

    /**
     * Expects the venue to carry `courts_count` (via withCount) and, when the
     * search was geographic, the computed `distance_km` column.
     */
    public static function fromModel(Venue $venue, bool $hasGoldenCourt = false): self
    {
        return new self(
            id: $venue->id,
            name: $venue->name,
            slug: $venue->slug,
            city: $venue->city,
            aggregateScore: $venue->aggregate_score,
            reviewCount: $venue->review_count,
            courtCount: $venue->courts_count ?? $venue->courts->count(),
            distanceKm: $venue->distanceKm() === null ? null : round($venue->distanceKm(), 1),
            hasGoldenCourt: $hasGoldenCourt,
        );
    }
}
