<?php

declare(strict_types=1);

namespace App\Support\Seo;

use Illuminate\Support\Arr;

/**
 * Site-wide facts every page's head tags share. Built once from config and
 * bound as a singleton, so builders and tests can construct it directly.
 */
final readonly class Site
{
    public function __construct(
        public string $name,
        public string $baseUrl,
        public string $description,
        public string $locale,
        public string $ogLocale,
        public ?OgImageData $image = null,
        public ?string $twitterSite = null,
    ) {}

    /**
     * An absolute URL on this site. Built from APP_URL rather than the current
     * request, so canonicals and sitemap entries are identical behind any
     * proxy or alternative host name. Empty query values are dropped.
     *
     * @param  array<string, string|int|float|bool|null>  $query
     */
    public function url(string $path = '/', array $query = []): string
    {
        $url = rtrim($this->baseUrl, '/').'/'.ltrim($path, '/');
        $query = array_filter($query, static fn (mixed $value): bool => $value !== null && $value !== '');

        return $query === [] ? $url : $url.'?'.Arr::query($query);
    }
}
