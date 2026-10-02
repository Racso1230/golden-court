<?php

declare(strict_types=1);

namespace App\Domain\Venues\Actions;

use App\Domain\Courts\Data\CourtSummaryData;
use App\Domain\Venues\Data\VenueDetailData;
use App\Support\Seo\JsonLd;
use App\Support\Seo\Site;

/**
 * A venue as schema.org structured data: a sports location with its address,
 * position, contact details, courts and, once reviewed, its rating. Built
 * from the read model so it can never trigger a query of its own.
 */
final readonly class BuildVenueJsonLd
{
    public function __construct(private Site $site) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(VenueDetailData $venue): array
    {
        $url = $this->site->url('/venues/'.$venue->slug);

        $data = [
            '@context' => JsonLd::CONTEXT,
            '@type' => 'SportsActivityLocation',
            '@id' => $url,
            'name' => $venue->name,
            'url' => $url,
            'sport' => 'Padel',
        ];

        if ($venue->description !== null) {
            $data['description'] = $venue->description;
        }

        $data['address'] = [
            '@type' => 'PostalAddress',
            'streetAddress' => $venue->addressLine2 === null
                ? $venue->addressLine1
                : sprintf('%s, %s', $venue->addressLine1, $venue->addressLine2),
            'addressLocality' => $venue->city,
            'postalCode' => $venue->postcode,
            'addressCountry' => $venue->countryCode,
        ];

        $data['geo'] = [
            '@type' => 'GeoCoordinates',
            'latitude' => $venue->latitude,
            'longitude' => $venue->longitude,
        ];

        if ($venue->phone !== null) {
            $data['telephone'] = $venue->phone;
        }

        if ($venue->website !== null) {
            $data['sameAs'] = [$venue->website];
        }

        if ($venue->courts !== []) {
            $data['containsPlace'] = array_map(
                fn (CourtSummaryData $court): array => [
                    '@type' => 'SportsActivityLocation',
                    'name' => $court->name,
                    'url' => $this->site->url(sprintf('/venues/%s/courts/%s', $venue->slug, $court->slug)),
                ],
                $venue->courts,
            );
        }

        if ($venue->reviewCount > 0) {
            $data['aggregateRating'] = JsonLd::aggregateRating($venue->aggregateScore, $venue->reviewCount);
        }

        return $data;
    }
}
