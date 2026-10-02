<?php

declare(strict_types=1);

namespace App\Support\Seo;

/**
 * The image shown when a page is shared. The URL is absolute.
 */
final readonly class OgImageData
{
    public function __construct(
        public string $url,
        public int $width,
        public int $height,
        public string $alt,
    ) {}
}
