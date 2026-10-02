<?php

declare(strict_types=1);

use App\Domain\Venues\Actions\BuildHomePageMeta;
use App\Support\Seo\RobotsDirective;

it('describes the site without repeating its name and offers a sitelinks search', function (): void {
    $meta = (new BuildHomePageMeta(seoSite()))->handle();

    expect($meta->title)->toBe('Golden Court: padel court reviews and ratings')
        ->and($meta->brandSuffix)->toBeFalse()
        ->and($meta->robots)->toBe(RobotsDirective::Index)
        ->and($meta->canonical)->toBe('https://golden-court.test/')
        ->and($meta->description)->toBe('Padel court reviews by players.')
        ->and($meta->jsonLd['website']['@type'] ?? null)->toBe('WebSite')
        ->and($meta->jsonLd['website']['potentialAction']['target']['urlTemplate'] ?? null)
        ->toBe('https://golden-court.test/venues?term={search_term_string}');
});
