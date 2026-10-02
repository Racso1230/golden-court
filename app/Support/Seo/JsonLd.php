<?php

declare(strict_types=1);

namespace App\Support\Seo;

/**
 * Helpers for schema.org structured data. Page-specific graphs are built by
 * Actions in the owning bounded context; this class only knows the format.
 */
final class JsonLd
{
    public const string CONTEXT = 'https://schema.org';

    /**
     * Encoded for an inline script element: `<`, `>` and `&` become unicode
     * escapes, so no value can close the element or open a comment whatever a
     * user typed into a review.
     *
     * @param  array<string, mixed>  $data
     */
    public static function encode(array $data): string
    {
        return json_encode(
            $data,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_PRESERVE_ZERO_FRACTION,
        );
    }

    /**
     * The rating shown on the page. The site's aggregate is a Bayesian average
     * by default; search engines require the marked-up value to match the
     * visible one, so that is what is emitted, with the true review count.
     *
     * @return array<string, mixed>
     */
    public static function aggregateRating(float $value, int $reviewCount): array
    {
        return [
            '@type' => 'AggregateRating',
            'ratingValue' => round($value, 1),
            'bestRating' => 5,
            'worstRating' => 1,
            'ratingCount' => $reviewCount,
            'reviewCount' => $reviewCount,
        ];
    }

    /**
     * @param  list<array{name: string, url: string}>  $crumbs
     * @return array<string, mixed>
     */
    public static function breadcrumbs(array $crumbs): array
    {
        return [
            '@context' => self::CONTEXT,
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(
                static fn (array $crumb, int $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $crumb['name'],
                    'item' => $crumb['url'],
                ],
                $crumbs,
                array_keys($crumbs),
            ),
        ];
    }

    /**
     * The site as a whole, with the search box crawlers may offer directly.
     *
     * @return array<string, mixed>
     */
    public static function webSite(Site $site, string $searchUrlTemplate): array
    {
        return [
            '@context' => self::CONTEXT,
            '@type' => 'WebSite',
            'name' => $site->name,
            'url' => $site->url('/'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $searchUrlTemplate,
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }
}
