<?php

declare(strict_types=1);

namespace App\Domain\Courts\Data;

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Data\DimensionAveragesData;
use App\Domain\Venues\Data\PostalAddressData;
use App\Domain\Venues\ValueObjects\Coordinates;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * A court page: the summary, its venue (name, location and website, so the
 * page and its structured data can place the court) and per-dimension
 * averages. The paginated reviews travel as a separate prop so pagination
 * keeps Laravel's paginator shape.
 */
#[TypeScript]
final class CourtDetailData extends Data
{
    public function __construct(
        public CourtSummaryData $court,
        public int $venueId,
        public string $venueName,
        public string $venueSlug,
        public string $venueCity,
        public PostalAddressData $venueAddress,
        public Coordinates $venueCoordinates,
        public ?string $venueWebsite,
        public DimensionAveragesData $averages,
    ) {}

    /**
     * Expects `venue` to be eager loaded on the court.
     */
    public static function fromModel(Court $court, bool $isGoldenCourt, DimensionAveragesData $averages): self
    {
        $venue = $court->venue;

        return new self(
            court: CourtSummaryData::fromModel($court, $isGoldenCourt),
            venueId: $venue->id,
            venueName: $venue->name,
            venueSlug: $venue->slug,
            venueCity: $venue->city,
            venueAddress: PostalAddressData::fromModel($venue),
            venueCoordinates: Coordinates::from($venue->latitude, $venue->longitude),
            venueWebsite: $venue->website,
            averages: $averages,
        );
    }
}
