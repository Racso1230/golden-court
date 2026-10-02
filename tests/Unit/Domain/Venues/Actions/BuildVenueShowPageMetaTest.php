<?php

declare(strict_types=1);

use App\Domain\Venues\Actions\BuildVenueJsonLd;
use App\Domain\Venues\Actions\BuildVenueShowPageMeta;
use App\Support\Seo\RobotsDirective;

function venueShowMeta(): BuildVenueShowPageMeta
{
    return new BuildVenueShowPageMeta(seoSite(), new BuildVenueJsonLd(seoSite()));
}

it('titles the page with the venue and city and uses its own description', function (): void {
    $meta = venueShowMeta()->handle(venueDetailFixture());

    expect($meta->title)->toBe('Harbourside Padel, Bristol – padel courts and reviews')
        ->and($meta->description)->toBe('Four indoor courts by the water.')
        ->and($meta->canonical)->toBe('https://golden-court.test/venues/harbourside-padel')
        ->and($meta->robots)->toBe(RobotsDirective::Index)
        ->and($meta->jsonLd)->toHaveKeys(['venue', 'breadcrumbs'])
        ->and($meta->jsonLd['breadcrumbs']['itemListElement'][2]['name'] ?? null)->toBe('Harbourside Padel');
});

it('writes a description from the facts when the venue has none', function (): void {
    expect(venueShowMeta()->handle(venueDetailFixture(['description' => null]))->description)
        ->toBe('Harbourside Padel is a padel venue in Bristol with 1 court. Rated 4.3/5 from 12 player reviews.')
        ->and(venueShowMeta()->handle(venueDetailFixture(['description' => null, 'reviewCount' => 0, 'courtCount' => 3]))->description)
        ->toBe('Harbourside Padel is a padel venue in Bristol with 3 courts. No player reviews yet.');
});
