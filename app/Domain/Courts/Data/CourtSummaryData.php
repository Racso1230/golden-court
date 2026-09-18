<?php

declare(strict_types=1);

namespace App\Domain\Courts\Data;

use App\Domain\Courts\Enums\CourtType;
use App\Domain\Courts\Enums\Surface;
use App\Domain\Courts\Enums\WallType;
use App\Domain\Courts\Models\Court;
use Spatie\LaravelData\Data;

final class CourtSummaryData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public CourtType $courtType,
        public string $courtTypeLabel,
        public WallType $wallType,
        public string $wallTypeLabel,
        public Surface $surface,
        public string $surfaceLabel,
        public float $aggregateScore,
        public int $reviewCount,
        public bool $isGoldenCourt,
    ) {}

    public static function fromModel(Court $court, bool $isGoldenCourt = false): self
    {
        return new self(
            id: $court->id,
            name: $court->name,
            slug: $court->slug,
            courtType: $court->court_type,
            courtTypeLabel: $court->court_type->label(),
            wallType: $court->wall_type,
            wallTypeLabel: $court->wall_type->label(),
            surface: $court->surface,
            surfaceLabel: $court->surface->label(),
            aggregateScore: $court->aggregate_score,
            reviewCount: $court->review_count,
            isGoldenCourt: $isGoldenCourt,
        );
    }
}
