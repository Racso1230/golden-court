<?php

declare(strict_types=1);

namespace App\Domain\Venues\Data;

use App\Domain\Courts\Data\CourtSummaryData;
use App\Domain\Courts\Models\Court;
use App\Domain\Venues\Models\Venue;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * A venue page: the summary plus address, contact details and its courts.
 */
#[TypeScript]
final class VenueDetailData extends Data
{
    /**
     * @param  array<int, CourtSummaryData>  $courts
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public ?string $description,
        public string $addressLine1,
        public ?string $addressLine2,
        public string $city,
        public string $postcode,
        public string $countryCode,
        public float $latitude,
        public float $longitude,
        public ?string $website,
        public ?string $phone,
        public float $aggregateScore,
        public int $reviewCount,
        public int $courtCount,
        public ?string $ownerDisplayName,
        public array $courts,
    ) {}

    /**
     * Expects `courts` and `owner` to be eager loaded.
     *
     * @param  Court|null  $goldenCourt  The city's Golden Court, if any, so courts can carry the badge.
     */
    public static function fromModel(Venue $venue, ?Court $goldenCourt = null): self
    {
        $courts = $venue->courts
            ->map(fn (Court $court): CourtSummaryData => CourtSummaryData::fromModel($court, $goldenCourt?->is($court) ?? false))
            ->values()
            ->all();

        return new self(
            id: $venue->id,
            name: $venue->name,
            slug: $venue->slug,
            description: $venue->description,
            addressLine1: $venue->address_line_1,
            addressLine2: $venue->address_line_2,
            city: $venue->city,
            postcode: $venue->postcode,
            countryCode: $venue->country_code,
            latitude: $venue->latitude,
            longitude: $venue->longitude,
            website: $venue->website,
            phone: $venue->phone,
            aggregateScore: $venue->aggregate_score,
            reviewCount: $venue->review_count,
            courtCount: count($courts),
            ownerDisplayName: $venue->owner?->display_name,
            courts: $courts,
        );
    }
}
