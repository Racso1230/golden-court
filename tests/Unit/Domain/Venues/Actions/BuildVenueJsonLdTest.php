<?php

declare(strict_types=1);

use App\Domain\Courts\Data\CourtSummaryData;
use App\Domain\Courts\Enums\CourtType;
use App\Domain\Courts\Enums\Surface;
use App\Domain\Courts\Enums\WallType;
use App\Domain\Venues\Actions\BuildVenueJsonLd;
use App\Domain\Venues\Data\VenueDetailData;

/**
 * @param  array<string, mixed>  $overrides
 */
function venueDetailFixture(array $overrides = []): VenueDetailData
{
    $court = new CourtSummaryData(
        id: 1,
        name: 'Court 1',
        slug: 'court-1',
        courtType: CourtType::Indoor,
        courtTypeLabel: 'Indoor',
        wallType: WallType::Panoramic,
        wallTypeLabel: 'Panoramic',
        surface: Surface::ArtificialGrass,
        surfaceLabel: 'Artificial grass',
        aggregateScore: 4.5,
        reviewCount: 8,
        isGoldenCourt: false,
    );

    return new VenueDetailData(...[
        'id' => 1,
        'name' => 'Harbourside Padel',
        'slug' => 'harbourside-padel',
        'description' => 'Four indoor courts by the water.',
        'addressLine1' => '1 Harbour Way',
        'addressLine2' => 'Unit 3',
        'city' => 'Bristol',
        'postcode' => 'BS1 4AA',
        'countryCode' => 'GB',
        'latitude' => 51.45,
        'longitude' => -2.6,
        'website' => 'https://harbourside.example',
        'phone' => '0117 000 0000',
        'aggregateScore' => 4.26,
        'reviewCount' => 12,
        'courtCount' => 1,
        'ownerDisplayName' => 'club_owner',
        'courts' => [$court],
        ...$overrides,
    ]);
}

it('describes a reviewed venue with its address, position, contact details, courts and rating', function (): void {
    $data = (new BuildVenueJsonLd(seoSite()))->handle(venueDetailFixture());

    expect($data['@type'])->toBe('SportsActivityLocation')
        ->and($data['@id'])->toBe('https://golden-court.test/venues/harbourside-padel')
        ->and($data['description'])->toBe('Four indoor courts by the water.')
        ->and($data['address'])->toBe([
            '@type' => 'PostalAddress',
            'streetAddress' => '1 Harbour Way, Unit 3',
            'addressLocality' => 'Bristol',
            'postalCode' => 'BS1 4AA',
            'addressCountry' => 'GB',
        ])
        ->and($data['geo'])->toBe(['@type' => 'GeoCoordinates', 'latitude' => 51.45, 'longitude' => -2.6])
        ->and($data['telephone'])->toBe('0117 000 0000')
        ->and($data['sameAs'])->toBe(['https://harbourside.example'])
        ->and($data['containsPlace'])->toBe([[
            '@type' => 'SportsActivityLocation',
            'name' => 'Court 1',
            'url' => 'https://golden-court.test/venues/harbourside-padel/courts/court-1',
        ]])
        ->and($data['aggregateRating'])->toBe([
            '@type' => 'AggregateRating',
            'ratingValue' => 4.3,
            'bestRating' => 5,
            'worstRating' => 1,
            'ratingCount' => 12,
            'reviewCount' => 12,
        ]);
});

it('leaves out what the venue does not have', function (): void {
    $data = (new BuildVenueJsonLd(seoSite()))->handle(venueDetailFixture([
        'description' => null,
        'addressLine2' => null,
        'website' => null,
        'phone' => null,
        'reviewCount' => 0,
        'aggregateScore' => 0.0,
        'courts' => [],
    ]));

    expect($data)->not->toHaveKeys(['description', 'telephone', 'sameAs', 'containsPlace', 'aggregateRating'])
        ->and($data['address']['streetAddress'])->toBe('1 Harbour Way');
});
