<?php

declare(strict_types=1);

use App\Domain\Courts\Models\Court;
use App\Domain\Users\Models\User;
use App\Domain\Venues\Models\Venue;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\get;

it('renders a venue with its courts and owner', function (): void {
    $owner = User::factory()->venueOwner()->create(['display_name' => 'club_owner']);
    $venue = Venue::factory()->claimedBy($owner)->create(['name' => 'Harbourside Padel']);
    Court::factory()->for($venue)->create(['name' => 'Court 1']);
    Court::factory()->for($venue)->create(['name' => 'Court 2']);

    get(route('venues.show', $venue))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Venues/Show')
            ->where('venue.name', 'Harbourside Padel')
            ->where('venue.slug', $venue->slug)
            ->where('venue.ownerDisplayName', 'club_owner')
            ->where('venue.courtCount', 2)
            ->has('venue.courts', 2)
            ->where('venue.courts.0.name', 'Court 1')
            ->where('venue.courts.0.isGoldenCourt', false));
});

it('flags the golden court of the city', function (): void {
    $venue = Venue::factory()->create(['city' => 'Manchester']);
    $golden = Court::factory()->for($venue)->create(['name' => 'Court 1']);
    Court::query()->whereKey($golden->id)->toBase()->update(['aggregate_score' => 4.8, 'review_count' => 12]);
    Court::factory()->for($venue)->create(['name' => 'Court 2']);

    get(route('venues.show', $venue))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('venue.courts.0.isGoldenCourt', true)
            ->where('venue.courts.1.isGoldenCourt', false));
});

it('404s for an unknown slug', function (): void {
    get('/venues/nowhere-padel')->assertNotFound();
});

it('404s for a soft-deleted venue', function (): void {
    $venue = Venue::factory()->create();
    $venue->delete();

    get(route('venues.show', $venue))->assertNotFound();
});

it('describes the venue for search engines with structured data', function (): void {
    $venue = Venue::factory()->create(['name' => 'Harbourside Padel', 'city' => 'Bristol']);
    Court::factory()->for($venue)->create(['name' => 'Court 1']);
    $base = rtrim((string) config('app.url'), '/');

    get(route('venues.show', $venue))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('head', function (Collection $head) use ($venue, $base): bool {
                $tags = $head->all();
                $place = jsonLd($tags, 'venue');
                $crumbs = jsonLd($tags, 'breadcrumbs');

                return headTag($tags, 'title') === sprintf('<title data-inertia="title">Harbourside Padel, Bristol – padel courts and reviews | %s</title>', config('app.name'))
                    && headTag($tags, 'canonical') === sprintf('<link rel="canonical" href="%s/venues/%s" data-inertia="canonical">', $base, $venue->slug)
                    && ($place['@type'] ?? null) === 'SportsActivityLocation'
                    && ($place['address']['addressLocality'] ?? null) === 'Bristol'
                    && ($place['containsPlace'][0]['name'] ?? null) === 'Court 1'
                    && ! array_key_exists('aggregateRating', $place)
                    && count($crumbs['itemListElement'] ?? []) === 3;
            }));
});
