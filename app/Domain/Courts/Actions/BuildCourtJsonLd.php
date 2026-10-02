<?php

declare(strict_types=1);

namespace App\Domain\Courts\Actions;

use App\Domain\Courts\Data\CourtDetailData;
use App\Domain\Reviews\Data\ReviewData;
use App\Domain\Reviews\Enums\ReviewStatus;
use App\Support\Seo\JsonLd;
use App\Support\Seo\Site;

/**
 * A court as schema.org structured data: a sports location inside its venue,
 * with the court's characteristics, its displayed rating and the published
 * reviews on the current page. Built from read models only.
 */
final readonly class BuildCourtJsonLd
{
    public function __construct(private Site $site) {}

    /**
     * @param  list<ReviewData>  $reviews  The reviews shown on the current page.
     * @return array<string, mixed>
     */
    public function handle(CourtDetailData $detail, array $reviews): array
    {
        $court = $detail->court;
        $venueUrl = $this->site->url('/venues/'.$detail->venueSlug);
        $url = $this->site->url(sprintf('/venues/%s/courts/%s', $detail->venueSlug, $court->slug));
        $address = $detail->venueAddress;

        $data = [
            '@context' => JsonLd::CONTEXT,
            '@type' => 'SportsActivityLocation',
            '@id' => $url,
            'name' => sprintf('%s at %s', $court->name, $detail->venueName),
            'url' => $url,
            'sport' => 'Padel',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $address->line2 === null ? $address->line1 : sprintf('%s, %s', $address->line1, $address->line2),
                'addressLocality' => $address->city,
                'postalCode' => $address->postcode,
                'addressCountry' => $address->countryCode,
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => $detail->venueCoordinates->latitude,
                'longitude' => $detail->venueCoordinates->longitude,
            ],
            'containedInPlace' => [
                '@type' => 'SportsActivityLocation',
                '@id' => $venueUrl,
                'name' => $detail->venueName,
                'url' => $venueUrl,
            ],
            'additionalProperty' => [
                ['@type' => 'PropertyValue', 'name' => 'Court type', 'value' => $court->courtTypeLabel],
                ['@type' => 'PropertyValue', 'name' => 'Walls', 'value' => $court->wallTypeLabel],
                ['@type' => 'PropertyValue', 'name' => 'Surface', 'value' => $court->surfaceLabel],
            ],
        ];

        if ($court->reviewCount > 0) {
            $data['aggregateRating'] = JsonLd::aggregateRating($court->aggregateScore, $court->reviewCount);
        }

        $published = array_values(array_filter(
            $reviews,
            static fn (ReviewData $review): bool => $review->status === ReviewStatus::Published,
        ));

        if ($published !== []) {
            $data['review'] = array_map(static fn (ReviewData $review): array => [
                '@type' => 'Review',
                'author' => ['@type' => 'Person', 'name' => $review->authorDisplayName],
                'datePublished' => $review->createdAt->toDateString(),
                'reviewBody' => $review->body,
                'reviewRating' => [
                    '@type' => 'Rating',
                    'ratingValue' => $review->overall,
                    'bestRating' => 5,
                    'worstRating' => 1,
                ],
            ], $published);
        }

        return $data;
    }
}
