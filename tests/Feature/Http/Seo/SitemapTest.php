<?php

declare(strict_types=1);

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Models\Review;
use App\Domain\Venues\Actions\BuildSitemapAction;
use App\Domain\Venues\Models\Venue;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\get;

/**
 * @return list<array{loc: string, lastmod: ?string}>
 */
function sitemapEntries(string $xml): array
{
    $document = simplexml_load_string($xml);
    expect($document)->not->toBeFalse();

    $entries = [];

    foreach ($document->url ?? [] as $url) {
        $entries[] = ['loc' => (string) $url->loc, 'lastmod' => isset($url->lastmod) ? (string) $url->lastmod : null];
    }

    return $entries;
}

it('lists the home page, the venue listing and every visible venue and court', function (): void {
    $venue = Venue::factory()->create();
    $court = Court::factory()->for($venue)->create();
    $base = rtrim((string) config('app.url'), '/');

    $response = get(route('sitemap'))->assertOk();

    expect($response->headers->get('Content-Type'))->toStartWith('application/xml')
        ->and(array_column(sitemapEntries((string) $response->getContent()), 'loc'))->toBe([
            $base.'/',
            $base.'/venues',
            sprintf('%s/venues/%s', $base, $venue->slug),
            sprintf('%s/venues/%s/courts/%s', $base, $venue->slug, $court->slug),
        ]);
});

it('leaves out soft-deleted venues and courts', function (): void {
    $gone = Venue::factory()->create();
    Court::factory()->for($gone)->create();
    $gone->delete();

    $kept = Venue::factory()->create();
    $deletedCourt = Court::factory()->for($kept)->create();
    $deletedCourt->delete();

    $locs = array_column(sitemapEntries((string) get(route('sitemap'))->getContent()), 'loc');

    expect(collect($locs)->contains(fn (string $loc): bool => str_contains($loc, $gone->slug)))->toBeFalse()
        ->and(collect($locs)->contains(fn (string $loc): bool => str_contains($loc, '/courts/')))->toBeFalse()
        ->and(collect($locs)->contains(fn (string $loc): bool => str_ends_with($loc, '/venues/'.$kept->slug)))->toBeTrue();
});

it('dates a court by its newest published review', function (): void {
    $court = Court::factory()->create();
    DB::table('courts')->where('id', $court->id)->update(['updated_at' => '2026-01-01 00:00:00']);
    DB::table('venues')->where('id', $court->venue_id)->update(['updated_at' => '2026-01-01 00:00:00']);
    Review::factory()->for($court)->create(['created_at' => '2026-05-01 12:00:00']);
    Review::factory()->for($court)->pending()->create(['created_at' => '2026-08-01 12:00:00']);

    $entries = sitemapEntries((string) get(route('sitemap'))->getContent());
    $courtEntry = collect($entries)->first(fn (array $entry): bool => str_contains($entry['loc'], '/courts/'));

    if ($courtEntry === null) {
        throw new RuntimeException('No court entry in the sitemap.');
    }

    expect($courtEntry['loc'])->toContain('/courts/')
        ->and(CarbonImmutable::parse((string) $courtEntry['lastmod'])->toDateString())->toBe('2026-05-01')
        ->and($entries[0]['lastmod'])->toBe($courtEntry['lastmod']);
});

it('serves the sitemap from the cache until it expires', function (): void {
    get(route('sitemap'));
    $venue = Venue::factory()->create();

    expect((string) get(route('sitemap'))->getContent())->not->toContain($venue->slug);

    cache()->forget(BuildSitemapAction::CACHE_KEY);

    expect((string) get(route('sitemap'))->getContent())->toContain($venue->slug);
});
