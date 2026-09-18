<?php

declare(strict_types=1);

use App\Domain\Courts\Models\Court;
use App\Domain\Users\Models\User;
use App\Domain\Venues\Models\Venue;
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
