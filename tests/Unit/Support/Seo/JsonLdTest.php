<?php

declare(strict_types=1);

use App\Support\Seo\JsonLd;

it('escapes characters that could close the script element', function (): void {
    $json = JsonLd::encode(['body' => '</script><!-- & more']);

    expect(str_contains($json, '<'))->toBeFalse()
        ->and(str_contains($json, '>'))->toBeFalse()
        ->and(str_contains($json, '&'))->toBeFalse()
        ->and(json_decode($json, true, flags: JSON_THROW_ON_ERROR))->toBe(['body' => '</script><!-- & more']);
});

it('keeps slashes, unicode and whole-number floats readable', function (): void {
    $json = JsonLd::encode(['url' => 'https://golden-court.test/venues/café', 'ratingValue' => 4.0]);

    expect($json)->toContain('https://golden-court.test/venues/café')
        ->and($json)->toContain('"ratingValue":4.0');
});

it('numbers breadcrumbs from one', function (): void {
    $list = JsonLd::breadcrumbs([
        ['name' => 'Home', 'url' => 'https://golden-court.test/'],
        ['name' => 'Venues', 'url' => 'https://golden-court.test/venues'],
    ]);

    expect($list['@type'])->toBe('BreadcrumbList')
        ->and($list['itemListElement'])->toBe([
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => 'https://golden-court.test/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Venues', 'item' => 'https://golden-court.test/venues'],
        ]);
});

it('describes the site search action', function (): void {
    $site = JsonLd::webSite(seoSite(), 'https://golden-court.test/venues?term={search_term_string}');

    expect($site['@type'])->toBe('WebSite')
        ->and($site['url'])->toBe('https://golden-court.test/')
        ->and($site['potentialAction'])->toBe([
            '@type' => 'SearchAction',
            'target' => ['@type' => 'EntryPoint', 'urlTemplate' => 'https://golden-court.test/venues?term={search_term_string}'],
            'query-input' => 'required name=search_term_string',
        ]);
});
