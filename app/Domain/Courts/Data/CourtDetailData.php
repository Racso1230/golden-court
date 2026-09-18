<?php

declare(strict_types=1);

namespace App\Domain\Courts\Data;

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Data\DimensionAveragesData;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * A court page: the summary, its venue and per-dimension averages. The
 * paginated reviews travel as a separate prop so pagination keeps Laravel's
 * paginator shape.
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
        public DimensionAveragesData $averages,
    ) {}

    /**
     * Expects `venue` to be eager loaded on the court.
     */
    public static function fromModel(Court $court, bool $isGoldenCourt, DimensionAveragesData $averages): self
    {
        return new self(
            court: CourtSummaryData::fromModel($court, $isGoldenCourt),
            venueId: $court->venue->id,
            venueName: $court->venue->name,
            venueSlug: $court->venue->slug,
            venueCity: $court->venue->city,
            averages: $averages,
        );
    }
}
