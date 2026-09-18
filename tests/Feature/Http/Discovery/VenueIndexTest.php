<?php

declare(strict_types=1);

use App\Domain\Courts\Enums\CourtType;
use App\Domain\Courts\Models\Court;
use App\Domain\Venues\Models\Venue;
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
            ->where('filters.term', 'manchester')
            ->where('filters.court_type', 'indoor')
            ->where('filters.sort', 'name')
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
