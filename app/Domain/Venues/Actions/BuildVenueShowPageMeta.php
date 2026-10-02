<?php

declare(strict_types=1);

namespace App\Domain\Venues\Actions;

use App\Domain\Venues\Data\VenueDetailData;
use App\Support\Seo\JsonLd;
use App\Support\Seo\PageMetaData;
use App\Support\Seo\Site;
use Illuminate\Support\Str;

/**
 * Head metadata for a venue page.
 */
final readonly class BuildVenueShowPageMeta
{
    public function __construct(
        private Site $site,
        private BuildVenueJsonLd $jsonLd,
    ) {}

    public function handle(VenueDetailData $venue): PageMetaData
    {
        $url = $this->site->url('/venues/'.$venue->slug);

        return PageMetaData::indexable(
            title: sprintf('%s, %s – padel courts and reviews', $venue->name, $venue->city),
            description: $venue->description ?? $this->describe($venue),
            canonical: $url,
        )
            ->withJsonLd('venue', $this->jsonLd->handle($venue))
            ->withJsonLd('breadcrumbs', JsonLd::breadcrumbs([
                ['name' => 'Home', 'url' => $this->site->url('/')],
                ['name' => 'Venues', 'url' => $this->site->url('/venues')],
                ['name' => $venue->name, 'url' => $url],
            ]));
    }

    private function describe(VenueDetailData $venue): string
    {
        $summary = sprintf(
            '%s is a padel venue in %s with %d %s.',
            $venue->name,
            $venue->city,
            $venue->courtCount,
            Str::plural('court', $venue->courtCount),
        );

        if ($venue->reviewCount === 0) {
            return $summary.' No player reviews yet.';
        }

        return sprintf(
            '%s Rated %.1f/5 from %d player %s.',
            $summary,
            $venue->aggregateScore,
            $venue->reviewCount,
            Str::plural('review', $venue->reviewCount),
        );
    }
}
