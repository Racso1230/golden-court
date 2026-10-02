<?php

declare(strict_types=1);

use App\Support\Seo\PageMetaData;
use App\Support\Seo\RobotsDirective;

it('collapses whitespace in titles and descriptions', function (): void {
    $meta = PageMetaData::indexable("  Court  1\n at Venue ", "Some   text\twith\nbreaks", 'https://x/');

    expect($meta->title)->toBe('Court 1 at Venue')
        ->and($meta->description)->toBe('Some text with breaks');
});

it('cuts long descriptions at a word boundary within the limit', function (): void {
    $meta = PageMetaData::indexable('Title', str_repeat('padel ', 60), 'https://x/');
    $description = (string) $meta->description;

    expect(mb_strlen($description))->toBeLessThanOrEqual(PageMetaData::DESCRIPTION_MAX_LENGTH)
        ->and($description)->toEndWith('padel…');
});

it('leaves short descriptions alone', function (): void {
    expect(PageMetaData::indexable('Title', 'Short and sweet.', 'https://x/')->description)->toBe('Short and sweet.');
});

it('is immutable through its withers', function (): void {
    $original = PageMetaData::indexable('Title', 'Desc.', 'https://x/');
    $changed = $original
        ->withRobots(RobotsDirective::NoIndexFollow)
        ->withoutCanonical()
        ->withJsonLd('thing', ['@type' => 'Thing'])
        ->withoutBrandSuffix();

    expect($original->robots)->toBe(RobotsDirective::Index)
        ->and($original->canonical)->toBe('https://x/')
        ->and($original->jsonLd)->toBe([])
        ->and($original->brandSuffix)->toBeTrue()
        ->and($changed->robots)->toBe(RobotsDirective::NoIndexFollow)
        ->and($changed->canonical)->toBeNull()
        ->and($changed->jsonLd)->toHaveKey('thing')
        ->and($changed->brandSuffix)->toBeFalse();
});

it('marks only private pages as not public', function (): void {
    expect(RobotsDirective::Index->isPublic())->toBeTrue()
        ->and(RobotsDirective::NoIndexFollow->isPublic())->toBeTrue()
        ->and(RobotsDirective::NoIndex->isPublic())->toBeFalse()
        ->and(RobotsDirective::NoIndexFollow->isIndexable())->toBeFalse();
});
