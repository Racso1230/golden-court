<?php

declare(strict_types=1);

namespace App\Domain\Venues\Actions;

use App\Domain\Venues\Data\VenueSearchCriteria;
use App\Domain\Venues\Enums\VenueSort;
use App\Support\Seo\PageMetaData;
use App\Support\Seo\RobotsDirective;
use App\Support\Seo\Site;
use Illuminate\Support\Str;

/**
 * Head metadata for the venue index. The plain listing and its per-city
 * variants are landing pages worth indexing; search terms, geographic and
 * facet variants are endless, so they stay out of the index but keep their
 * links crawlable.
 */
final readonly class BuildVenueIndexPageMeta
{
    public function __construct(private Site $site) {}

    /**
     * @param  int  $total  Matching venues across all pages.
     * @param  int  $lastPage  The last page number the paginator reports.
     */
    public function handle(VenueSearchCriteria $criteria, int $total, int $lastPage): PageMetaData
    {
        $city = $criteria->city === null ? null : mb_convert_case(trim($criteria->city), MB_CASE_TITLE, 'UTF-8');
        $listing = $this->isPlainListing($criteria);
        $venues = sprintf('%d padel %s', $total, Str::plural('venue', $total));

        $title = match (true) {
            $criteria->term !== null => sprintf('Venues matching "%s"', $criteria->term),
            $criteria->near !== null => 'Padel venues near you',
            $city !== null => sprintf('Padel courts in %s', $city),
            default => 'Padel venues in the UK',
        };

        $description = $listing && $city !== null
            ? sprintf('%s in %s, each court rated by players on glass, lighting, turf and facilities.', $venues, $city)
            : sprintf('Browse %s rated by players on glass, lighting, turf and facilities. Filter by court type, walls, surface and score.', $venues);

        if (! $listing) {
            return PageMetaData::indexable($title, $description, $this->site->url('/venues'))
                ->withRobots(RobotsDirective::NoIndexFollow)
                ->withoutCanonical();
        }

        $meta = PageMetaData::indexable($title, $description, $this->site->url('/venues', [
            'city' => $city,
            'page' => $criteria->page > 1 ? $criteria->page : null,
        ]));

        return $total === 0 || $criteria->page > $lastPage
            ? $meta->withRobots(RobotsDirective::NoIndexFollow)
            : $meta;
    }

    private function isPlainListing(VenueSearchCriteria $criteria): bool
    {
        return $criteria->term === null
            && $criteria->near === null
            && ! $criteria->hasCourtFilters()
            && $criteria->minScore === null
            && $criteria->sort === VenueSort::Score;
    }
}
