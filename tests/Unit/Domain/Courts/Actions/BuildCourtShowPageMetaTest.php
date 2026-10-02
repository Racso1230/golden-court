<?php

declare(strict_types=1);

use App\Domain\Courts\Actions\BuildCourtJsonLd;
use App\Domain\Courts\Actions\BuildCourtShowPageMeta;
use App\Domain\Reviews\Data\DimensionAveragesData;
use App\Domain\Reviews\Enums\ReviewSort;
use App\Support\Seo\RobotsDirective;

function courtShowMeta(): BuildCourtShowPageMeta
{
    return new BuildCourtShowPageMeta(seoSite(), new BuildCourtJsonLd(seoSite()));
}

it('titles and describes the court from its facts and averages', function (): void {
    $meta = courtShowMeta()->handle(courtDetailFixture(), [], ReviewSort::Recent, page: 1, lastPage: 1);

    expect($meta->title)->toBe('Court 1 at Harbourside Padel, Bristol – reviews')
        ->and($meta->description)->toBe('Court 1 at Harbourside Padel (Bristol): indoor, panoramic walls, artificial grass. Rated 4.5/5 by 8 players – glass 4.5, lighting 4.2, turf 4.8, facilities 3.9.')
        ->and($meta->canonical)->toBe('https://golden-court.test/venues/harbourside-padel/courts/court-1')
        ->and($meta->robots)->toBe(RobotsDirective::Index)
        ->and($meta->jsonLd)->toHaveKeys(['court', 'breadcrumbs'])
        ->and(count($meta->jsonLd['breadcrumbs']['itemListElement'] ?? []))->toBe(4);
});

it('says so when the court has no reviews', function (): void {
    $detail = courtDetailFixture(['reviewCount' => 0, 'aggregateScore' => 0.0], DimensionAveragesData::none());

    expect(courtShowMeta()->handle($detail, [], ReviewSort::Recent, page: 1, lastPage: 1)->description)
        ->toBe('Court 1 at Harbourside Padel (Bristol): indoor, panoramic walls, artificial grass. No player reviews yet.');
});

it('keeps the page in the canonical and re-sorted or overrun pages out of the index', function (): void {
    $second = courtShowMeta()->handle(courtDetailFixture(), [], ReviewSort::Recent, page: 2, lastPage: 3);
    $sorted = courtShowMeta()->handle(courtDetailFixture(), [], ReviewSort::Highest, page: 1, lastPage: 3);
    $overrun = courtShowMeta()->handle(courtDetailFixture(), [], ReviewSort::Recent, page: 9, lastPage: 3);

    expect($second->canonical)->toBe('https://golden-court.test/venues/harbourside-padel/courts/court-1?page=2')
        ->and($second->robots)->toBe(RobotsDirective::Index)
        ->and($sorted->robots)->toBe(RobotsDirective::NoIndexFollow)
        ->and($overrun->robots)->toBe(RobotsDirective::NoIndexFollow);
});
