<?php

declare(strict_types=1);

use App\Support\Seo\HeadTagRenderer;
use App\Support\Seo\OgImageData;
use App\Support\Seo\PageMetaData;
use App\Support\Seo\RobotsDirective;

it('renders an indexable page with title, description, canonical and sharing tags', function (): void {
    $tags = (new HeadTagRenderer(seoSite()))->render(
        PageMetaData::indexable('Harbourside Padel, Bristol', 'Four courts & a "bar".', 'https://golden-court.test/venues/harbourside'),
    );

    expect($tags)
        ->toContain('<title data-inertia="title">Harbourside Padel, Bristol | Golden Court</title>')
        ->toContain('<meta name="robots" content="index, follow" data-inertia="robots">')
        ->toContain('<meta name="description" content="Four courts &amp; a &quot;bar&quot;." data-inertia="description">')
        ->toContain('<link rel="canonical" href="https://golden-court.test/venues/harbourside" data-inertia="canonical">')
        ->toContain('<meta property="og:title" content="Harbourside Padel, Bristol" data-inertia="og:title">')
        ->toContain('<meta property="og:url" content="https://golden-court.test/venues/harbourside" data-inertia="og:url">')
        ->toContain('<meta property="og:locale" content="en_GB" data-inertia="og:locale">')
        ->toContain('<meta name="twitter:card" content="summary" data-inertia="twitter:card">');
});

it('says nothing but the title and robots for a private page', function (): void {
    $tags = (new HeadTagRenderer(seoSite()))->render(PageMetaData::noindex('Dashboard'));

    expect($tags)->toBe([
        '<title data-inertia="title">Dashboard | Golden Court</title>',
        '<meta name="robots" content="noindex, nofollow" data-inertia="robots">',
    ]);
});

it('keeps sharing tags for a noindex,follow page and drops the canonical when asked', function (): void {
    $tags = (new HeadTagRenderer(seoSite()))->render(
        PageMetaData::indexable('Venues matching padel', 'Results.', 'https://golden-court.test/venues')
            ->withRobots(RobotsDirective::NoIndexFollow)
            ->withoutCanonical(),
    );

    expect($tags)
        ->toContain('<meta name="robots" content="noindex, follow" data-inertia="robots">')
        ->toContain('<meta property="og:title" content="Venues matching padel" data-inertia="og:title">')
        ->and(headTag($tags, 'canonical'))->toBeNull()
        ->and(headTag($tags, 'og:url'))->toBeNull();
});

it('omits the brand suffix when told to', function (): void {
    $tags = (new HeadTagRenderer(seoSite()))->render(
        PageMetaData::indexable('Golden Court: padel court reviews', 'Desc.', 'https://golden-court.test/')->withoutBrandSuffix(),
    );

    expect(headTag($tags, 'title'))->toBe('<title data-inertia="title">Golden Court: padel court reviews</title>');
});

it('uses the site image when the page has none and switches to a large card', function (): void {
    $image = new OgImageData('https://golden-court.test/images/og.png', 1200, 630, 'Golden Court');

    $tags = (new HeadTagRenderer(seoSite($image)))->render(
        PageMetaData::indexable('Home', 'Desc.', 'https://golden-court.test/'),
    );

    expect($tags)
        ->toContain('<meta property="og:image" content="https://golden-court.test/images/og.png" data-inertia="og:image">')
        ->toContain('<meta property="og:image:width" content="1200" data-inertia="og:image:width">')
        ->toContain('<meta property="og:image:alt" content="Golden Court" data-inertia="og:image:alt">')
        ->toContain('<meta name="twitter:card" content="summary_large_image" data-inertia="twitter:card">')
        ->toContain('<meta name="twitter:image" content="https://golden-court.test/images/og.png" data-inertia="twitter:image">');
});

it('renders JSON-LD blocks that cannot break out of their script element', function (): void {
    $tags = (new HeadTagRenderer(seoSite()))->render(
        PageMetaData::indexable('Court 1', 'Desc.', 'https://golden-court.test/x')
            ->withJsonLd('review', ['@type' => 'Review', 'reviewBody' => 'Nice </script><script>alert(1)</script>']),
    );

    $script = headTag($tags, 'ld:review');

    expect($script)->not->toBeNull()
        ->and(substr_count((string) $script, '</script>'))->toBe(1)
        ->and($script)->not->toContain('<script>alert')
        ->and(jsonLd($tags, 'review'))->toBe(['@type' => 'Review', 'reviewBody' => 'Nice </script><script>alert(1)</script>']);
});
