<?php

declare(strict_types=1);

use App\Domain\Courts\Enums\CourtType;
use App\Domain\Venues\Actions\BuildVenueIndexPageMeta;
use App\Domain\Venues\Data\VenueSearchCriteria;
use App\Domain\Venues\Enums\VenueSort;
use App\Domain\Venues\ValueObjects\Coordinates;
use App\Support\Seo\RobotsDirective;

function venueIndexMeta(): BuildVenueIndexPageMeta
{
    return new BuildVenueIndexPageMeta(seoSite());
}

it('indexes the plain listing under /venues', function (): void {
    $meta = venueIndexMeta()->handle(new VenueSearchCriteria, total: 12, lastPage: 1);

    expect($meta->title)->toBe('Padel venues in the UK')
        ->and($meta->description)->toBe('Browse 12 padel venues rated by players on glass, lighting, turf and facilities. Filter by court type, walls, surface and score.')
        ->and($meta->canonical)->toBe('https://golden-court.test/venues')
        ->and($meta->robots)->toBe(RobotsDirective::Index);
});

it('keeps the page number in the canonical from page two', function (): void {
    expect(venueIndexMeta()->handle(new VenueSearchCriteria(page: 2), total: 30, lastPage: 2)->canonical)
        ->toBe('https://golden-court.test/venues?page=2');
});

it('indexes city listings under a normalised city', function (): void {
    $meta = venueIndexMeta()->handle(new VenueSearchCriteria(city: ' leeds '), total: 1, lastPage: 1);

    expect($meta->title)->toBe('Padel courts in Leeds')
        ->and($meta->description)->toBe('1 padel venue in Leeds, each court rated by players on glass, lighting, turf and facilities.')
        ->and($meta->canonical)->toBe('https://golden-court.test/venues?city=Leeds')
        ->and($meta->robots)->toBe(RobotsDirective::Index);
});

it('keeps empty listings and pages past the end out of the index', function (): void {
    expect(venueIndexMeta()->handle(new VenueSearchCriteria, total: 0, lastPage: 1)->robots)->toBe(RobotsDirective::NoIndexFollow)
        ->and(venueIndexMeta()->handle(new VenueSearchCriteria(page: 5), total: 30, lastPage: 2)->robots)->toBe(RobotsDirective::NoIndexFollow);
});

it('keeps searches, geographic and facet variants out of the index without a canonical', function (): void {
    $term = venueIndexMeta()->handle(new VenueSearchCriteria(term: 'padel'), total: 4, lastPage: 1);
    $near = venueIndexMeta()->handle(new VenueSearchCriteria(near: Coordinates::from(53.4, -2.2)), total: 4, lastPage: 1);
    $facet = venueIndexMeta()->handle(new VenueSearchCriteria(city: 'Leeds', courtType: CourtType::Indoor), total: 4, lastPage: 1);
    $sorted = venueIndexMeta()->handle(new VenueSearchCriteria(sort: VenueSort::Name), total: 4, lastPage: 1);

    expect($term->title)->toBe('Venues matching "padel"')
        ->and($near->title)->toBe('Padel venues near you')
        ->and($facet->title)->toBe('Padel courts in Leeds')
        ->and($sorted->title)->toBe('Padel venues in the UK');

    foreach ([$term, $near, $facet, $sorted] as $meta) {
        expect($meta->robots)->toBe(RobotsDirective::NoIndexFollow)
            ->and($meta->canonical)->toBeNull();
    }
});
