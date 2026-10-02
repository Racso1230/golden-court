<?php

declare(strict_types=1);

use App\Domain\Courts\Actions\BuildCourtJsonLd;
use App\Domain\Courts\Data\CourtDetailData;
use App\Domain\Courts\Data\CourtSummaryData;
use App\Domain\Courts\Enums\CourtType;
use App\Domain\Courts\Enums\Surface;
use App\Domain\Courts\Enums\WallType;
use App\Domain\Reviews\Data\DimensionAveragesData;
use App\Domain\Reviews\Data\ReviewData;
use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Venues\Data\PostalAddressData;
use App\Domain\Venues\ValueObjects\Coordinates;
use Carbon\CarbonImmutable;

/**
 * @param  array<string, mixed>  $court
 */
function courtDetailFixture(array $court = [], ?DimensionAveragesData $averages = null): CourtDetailData
{
    return new CourtDetailData(
        court: new CourtSummaryData(...[
            'id' => 1,
            'name' => 'Court 1',
            'slug' => 'court-1',
            'courtType' => CourtType::Indoor,
            'courtTypeLabel' => 'Indoor',
            'wallType' => WallType::Panoramic,
            'wallTypeLabel' => 'Panoramic',
            'surface' => Surface::ArtificialGrass,
            'surfaceLabel' => 'Artificial grass',
            'aggregateScore' => 4.46,
            'reviewCount' => 8,
            'isGoldenCourt' => true,
            ...$court,
        ]),
        venueId: 1,
        venueName: 'Harbourside Padel',
        venueSlug: 'harbourside-padel',
        venueCity: 'Bristol',
        venueAddress: new PostalAddressData('1 Harbour Way', null, 'Bristol', 'BS1 4AA', 'GB'),
        venueCoordinates: Coordinates::from(51.45, -2.6),
        venueWebsite: 'https://harbourside.example',
        averages: $averages ?? new DimensionAveragesData(4.5, 4.2, 4.8, 3.9),
    );
}

/**
 * @param  array<string, mixed>  $overrides
 */
function reviewDataFixture(array $overrides = []): ReviewData
{
    return new ReviewData(...[
        'id' => 1,
        'courtId' => 1,
        'scores' => ['glass' => 5, 'lighting' => 4, 'turf' => 4, 'facilities' => 4],
        'overall' => 4.25,
        'body' => 'Great glass, good lights.',
        'status' => ReviewStatus::Published,
        'authorDisplayName' => 'padel_pat',
        'playedOn' => null,
        'createdAt' => CarbonImmutable::parse('2026-09-20 10:00:00'),
        'updatedAt' => CarbonImmutable::parse('2026-09-20 10:00:00'),
        'helpfulCount' => 3,
        'hasVoted' => false,
        'isAuthor' => false,
        'reply' => null,
        ...$overrides,
    ]);
}

it('places the court in its venue with its characteristics, rating and published reviews', function (): void {
    $data = (new BuildCourtJsonLd(seoSite()))->handle(courtDetailFixture(), [
        reviewDataFixture(),
        reviewDataFixture(['id' => 2, 'status' => ReviewStatus::Pending, 'body' => 'Not yet public.']),
    ]);

    expect($data['@type'])->toBe('SportsActivityLocation')
        ->and($data['@id'])->toBe('https://golden-court.test/venues/harbourside-padel/courts/court-1')
        ->and($data['name'])->toBe('Court 1 at Harbourside Padel')
        ->and($data['address']['addressLocality'])->toBe('Bristol')
        ->and($data['geo'])->toBe(['@type' => 'GeoCoordinates', 'latitude' => 51.45, 'longitude' => -2.6])
        ->and($data['containedInPlace']['@id'])->toBe('https://golden-court.test/venues/harbourside-padel')
        ->and($data['additionalProperty'][1])->toBe(['@type' => 'PropertyValue', 'name' => 'Walls', 'value' => 'Panoramic'])
        ->and($data['aggregateRating']['ratingValue'])->toBe(4.5)
        ->and($data['aggregateRating']['ratingCount'])->toBe(8)
        ->and($data['review'])->toBe([[
            '@type' => 'Review',
            'author' => ['@type' => 'Person', 'name' => 'padel_pat'],
            'datePublished' => '2026-09-20',
            'reviewBody' => 'Great glass, good lights.',
            'reviewRating' => ['@type' => 'Rating', 'ratingValue' => 4.25, 'bestRating' => 5, 'worstRating' => 1],
        ]]);
});

it('omits the rating and reviews for an unreviewed court', function (): void {
    $data = (new BuildCourtJsonLd(seoSite()))->handle(courtDetailFixture(['reviewCount' => 0, 'aggregateScore' => 0.0]), []);

    expect($data)->not->toHaveKeys(['aggregateRating', 'review']);
});
