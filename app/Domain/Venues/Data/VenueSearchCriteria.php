<?php

declare(strict_types=1);

namespace App\Domain\Venues\Data;

use App\Domain\Courts\Enums\CourtType;
use App\Domain\Courts\Enums\Surface;
use App\Domain\Courts\Enums\WallType;
use App\Domain\Venues\Enums\VenueSort;
use App\Domain\Venues\ValueObjects\Coordinates;
use Spatie\LaravelData\Data;

/**
 * Everything a venue search can be narrowed and ordered by. Knows nothing
 * about HTTP; the request class builds it.
 */
final class VenueSearchCriteria extends Data
{
    public const int DEFAULT_RADIUS_KM = 25;

    public function __construct(
        public ?string $term = null,
        public ?string $city = null,
        public ?Coordinates $near = null,
        public int $radiusKm = self::DEFAULT_RADIUS_KM,
        public ?CourtType $courtType = null,
        public ?WallType $wallType = null,
        public ?Surface $surface = null,
        public ?float $minScore = null,
        public VenueSort $sort = VenueSort::Score,
        public int $page = 1,
    ) {}

    public function hasCourtFilters(): bool
    {
        return $this->courtType !== null || $this->wallType !== null || $this->surface !== null;
    }
}
