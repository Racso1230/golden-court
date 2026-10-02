<?php

declare(strict_types=1);

namespace App\Domain\Venues\Actions;

use App\Domain\Courts\Models\Court;
use App\Domain\Venues\Models\Venue;
use App\Domain\Venues\Queries\SitemapVenuesQuery;
use App\Support\Seo\Site;
use App\Support\Seo\SitemapEntry;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Cache\Repository;

/**
 * The URLs crawlers should know about: the home page, the venue listing and
 * every venue and court, each dated by its last meaningful change. Cached
 * for an hour; at the 50,000-URL sitemap limit this would need an index file.
 */
final readonly class BuildSitemapAction
{
    public const string CACHE_KEY = 'seo:sitemap';

    public const int CACHE_SECONDS = 3600;

    public function __construct(
        private Site $site,
        private Repository $cache,
    ) {}

    /**
     * @return list<SitemapEntry>
     */
    public function handle(): array
    {
        /** @var list<SitemapEntry> */
        return $this->cache->remember(self::CACHE_KEY, self::CACHE_SECONDS, fn (): array => $this->build());
    }

    /**
     * @return list<SitemapEntry>
     */
    private function build(): array
    {
        $venueEntries = [];
        $newest = null;

        foreach ((new SitemapVenuesQuery)->get() as $venue) {
            $venueModified = $venue->updated_at;
            $courtEntries = [];

            foreach ($venue->courts as $court) {
                $courtModified = $this->latest($court->updated_at, $this->latestReview($court));
                $venueModified = $this->latest($venueModified, $courtModified);
                $courtEntries[] = new SitemapEntry($this->courtUrl($venue, $court), $courtModified);
            }

            $newest = $this->latest($newest, $venueModified);
            $venueEntries[] = new SitemapEntry($this->site->url('/venues/'.$venue->slug), $venueModified);
            array_push($venueEntries, ...$courtEntries);
        }

        return [
            new SitemapEntry($this->site->url('/'), $newest),
            new SitemapEntry($this->site->url('/venues'), $newest),
            ...$venueEntries,
        ];
    }

    private function courtUrl(Venue $venue, Court $court): string
    {
        return $this->site->url(sprintf('/venues/%s/courts/%s', $venue->slug, $court->slug));
    }

    private function latestReview(Court $court): ?CarbonImmutable
    {
        $value = SitemapVenuesQuery::latestReviewAt($court);

        return $value === null ? null : CarbonImmutable::parse($value);
    }

    private function latest(?CarbonImmutable $a, ?CarbonImmutable $b): ?CarbonImmutable
    {
        if ($a === null || $b === null) {
            return $a ?? $b;
        }

        return $a->greaterThan($b) ? $a : $b;
    }
}
