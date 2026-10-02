<?php

declare(strict_types=1);

use App\Domain\Courts\Enums\CourtType;
use App\Domain\Courts\Models\Court;
use App\Domain\Venues\Models\Venue;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\get;

it('renders the venue index with results, filters and options', function (): void {
    $venue = Venue::factory()->create(['name' => 'Northern Quarter Padel', 'city' => 'Manchester']);
    Court::factory()->for($venue)->create(['court_type' => CourtType::Indoor]);
    Venue::factory()->create(['name' => 'Harbourside Padel', 'city' => 'Bristol']);

    get(route('venues.index', ['term' => 'manchester', 'court_type' => 'indoor', 'sort' => 'name']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Venues/Index')
            ->has('venues.data', 1)
            ->where('venues.data.0.slug', $venue->slug)
            ->where('venues.data.0.courtCount', 1)
            ->where('venues.data.0.distanceKm', null)
            ->where('venues.total', 1)
            ->where('criteria.term', 'manchester')
            ->where('criteria.courtType', 'indoor')
            ->where('criteria.sort', 'name')
            ->has('options.courtTypes', 3)
            ->has('options.sorts', 4)
            ->where('options.sorts.0.label', 'Highest rated'));
});

it('returns distances when searching near a point', function (): void {
    Venue::factory()->create(['slug' => 'manchester', 'latitude' => 53.4808, 'longitude' => -2.2426]);
    Venue::factory()->create(['slug' => 'london', 'latitude' => 51.5074, 'longitude' => -0.1278]);

    get(route('venues.index', ['lat' => 53.48, 'lng' => -2.24, 'radius' => 30, 'sort' => 'distance']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Venues/Index')
            ->has('venues.data', 1)
            ->where('venues.data.0.slug', 'manchester')
            ->where('venues.data.0.distanceKm', fn (mixed $distance): bool => is_float($distance) && $distance < 1.0));
});

it('keeps the query string on pagination links', function (): void {
    Venue::factory()->count(21)->create(['city' => 'Leeds']);

    get(route('venues.index', ['city' => 'Leeds']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Venues/Index')
            ->has('venues.data', 20)
            ->where('venues.last_page', 2)
            ->where('venues.next_page_url', fn (mixed $url): bool => is_string($url) && str_contains($url, 'city=Leeds') && str_contains($url, 'page=2')));
});

it('rejects invalid search parameters', function (): void {
    get(route('venues.index', ['min_score' => 9, 'sort' => 'bogus', 'lat' => 10]))
        ->assertRedirect()
        ->assertSessionHasErrors(['min_score', 'sort', 'lng']);
});

it('is public', function (): void {
    get(route('venues.index'))->assertOk();
});

it('gives city listings a canonical that keeps the city and the page', function (): void {
    Venue::factory()->count(21)->create(['city' => 'Leeds']);
    $base = rtrim((string) config('app.url'), '/');

    get(route('venues.index', ['city' => 'Leeds', 'page' => 2]))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('head', fn (Collection $head): bool => headTag($head->all(), 'title') === sprintf('<title data-inertia="title">Padel courts in Leeds | %s</title>', config('app.name'))
                && headTag($head->all(), 'canonical') === sprintf('<link rel="canonical" href="%s/venues?city=Leeds&amp;page=2" data-inertia="canonical">', $base)
                && headTag($head->all(), 'robots') === '<meta name="robots" content="index, follow" data-inertia="robots">'));
});

it('keeps filtered searches out of the index', function (): void {
    get(route('venues.index', ['term' => 'padel', 'sort' => 'name']))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('head', fn (Collection $head): bool => headTag($head->all(), 'robots') === '<meta name="robots" content="noindex, follow" data-inertia="robots">'
                && headTag($head->all(), 'canonical') === null));
});
