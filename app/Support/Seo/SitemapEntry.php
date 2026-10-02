<?php

declare(strict_types=1);

namespace App\Support\Seo;

use Carbon\CarbonImmutable;

/**
 * One URL in sitemap.xml. The location is absolute.
 */
final readonly class SitemapEntry
{
    public function __construct(
        public string $loc,
        public ?CarbonImmutable $lastModified = null,
    ) {}
}
