<?php

declare(strict_types=1);

namespace App\Domain\Venues\Actions;

use App\Support\Seo\JsonLd;
use App\Support\Seo\PageMetaData;
use App\Support\Seo\Site;

/**
 * Head metadata for the home page: the site itself, with the search box
 * crawlers may surface as a sitelinks search.
 */
final readonly class BuildHomePageMeta
{
    public function __construct(private Site $site) {}

    public function handle(): PageMetaData
    {
        return PageMetaData::indexable(
            title: sprintf('%s: padel court reviews and ratings', $this->site->name),
            description: $this->site->description,
            canonical: $this->site->url('/'),
        )
            ->withoutBrandSuffix()
            ->withJsonLd('website', JsonLd::webSite(
                $this->site,
                $this->site->url('/venues').'?term={search_term_string}',
            ));
    }
}
