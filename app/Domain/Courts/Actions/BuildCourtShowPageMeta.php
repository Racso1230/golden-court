<?php

declare(strict_types=1);

namespace App\Domain\Courts\Actions;

use App\Domain\Courts\Data\CourtDetailData;
use App\Domain\Reviews\Data\ReviewData;
use App\Domain\Reviews\Enums\ReviewSort;
use App\Support\Seo\JsonLd;
use App\Support\Seo\PageMetaData;
use App\Support\Seo\RobotsDirective;
use App\Support\Seo\Site;
use Illuminate\Support\Str;

/**
 * Head metadata for a court page. Pages of reviews are distinct content and
 * keep their page number in the canonical; re-sorted views and pages past
 * the end stay out of the index.
 */
final readonly class BuildCourtShowPageMeta
{
    public function __construct(
        private Site $site,
        private BuildCourtJsonLd $jsonLd,
    ) {}

    /**
     * @param  list<ReviewData>  $reviews  The reviews shown on the current page.
     */
    public function handle(CourtDetailData $detail, array $reviews, ReviewSort $sort, int $page, int $lastPage): PageMetaData
    {
        $court = $detail->court;
        $path = sprintf('/venues/%s/courts/%s', $detail->venueSlug, $court->slug);

        $meta = PageMetaData::indexable(
            title: sprintf('%s at %s, %s – reviews', $court->name, $detail->venueName, $detail->venueCity),
            description: $this->describe($detail),
            canonical: $this->site->url($path, ['page' => $page > 1 ? $page : null]),
        )
            ->withJsonLd('court', $this->jsonLd->handle($detail, $reviews))
            ->withJsonLd('breadcrumbs', JsonLd::breadcrumbs([
                ['name' => 'Home', 'url' => $this->site->url('/')],
                ['name' => 'Venues', 'url' => $this->site->url('/venues')],
                ['name' => $detail->venueName, 'url' => $this->site->url('/venues/'.$detail->venueSlug)],
                ['name' => $court->name, 'url' => $this->site->url($path)],
            ]));

        return $sort !== ReviewSort::Recent || $page > $lastPage
            ? $meta->withRobots(RobotsDirective::NoIndexFollow)
            : $meta;
    }

    private function describe(CourtDetailData $detail): string
    {
        $court = $detail->court;

        $facts = sprintf(
            '%s at %s (%s): %s, %s walls, %s.',
            $court->name,
            $detail->venueName,
            $detail->venueCity,
            mb_strtolower($court->courtTypeLabel),
            mb_strtolower($court->wallTypeLabel),
            mb_strtolower($court->surfaceLabel),
        );

        if ($court->reviewCount === 0) {
            return $facts.' No player reviews yet.';
        }

        $averages = $detail->averages;
        $dimensions = implode(', ', array_filter([
            $averages->glass === null ? null : sprintf('glass %.1f', $averages->glass),
            $averages->lighting === null ? null : sprintf('lighting %.1f', $averages->lighting),
            $averages->turf === null ? null : sprintf('turf %.1f', $averages->turf),
            $averages->facilities === null ? null : sprintf('facilities %.1f', $averages->facilities),
        ]));

        return sprintf(
            '%s Rated %.1f/5 by %d %s%s.',
            $facts,
            $court->aggregateScore,
            $court->reviewCount,
            Str::plural('player', $court->reviewCount),
            $dimensions === '' ? '' : ' – '.$dimensions,
        );
    }
}
