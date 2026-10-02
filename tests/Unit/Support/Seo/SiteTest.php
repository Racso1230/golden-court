<?php

declare(strict_types=1);

use App\Support\Seo\Site;

it('builds absolute urls from the base url', function (): void {
    $site = seoSite();

    expect($site->url('/'))->toBe('https://golden-court.test/')
        ->and($site->url('venues/harbourside'))->toBe('https://golden-court.test/venues/harbourside')
        ->and($site->url('/venues', ['city' => 'Leeds', 'page' => 2]))->toBe('https://golden-court.test/venues?city=Leeds&page=2');
});

it('drops empty query values and encodes the rest', function (): void {
    expect(seoSite()->url('/venues', ['term' => null, 'city' => '', 'sort' => 'name', 'q' => 'a b']))
        ->toBe('https://golden-court.test/venues?sort=name&q=a%20b');
});

it('tolerates a trailing slash on the base url', function (): void {
    $site = new Site('Golden Court', 'https://golden-court.test/', 'Desc.', 'en-GB', 'en_GB');

    expect($site->url('/venues'))->toBe('https://golden-court.test/venues');
});
