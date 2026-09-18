<?php

declare(strict_types=1);

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Models\ReviewVote;
use App\Domain\Users\Models\User;
use App\Domain\Venues\Models\Venue;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('renders a court with averages and paginated reviews', function (): void {
    $court = Court::factory()->create(['name' => 'Court 3']);
    Review::factory()->count(6)->for($court)->create(['glass_rating' => 4, 'lighting_rating' => 4, 'turf_rating' => 4, 'facilities_rating' => 4]);
    Review::factory()->count(6)->for($court)->create(['glass_rating' => 5, 'lighting_rating' => 4, 'turf_rating' => 4, 'facilities_rating' => 4]);
    Review::factory()->for($court)->pending()->create();

    get(route('courts.show', [$court->venue, $court]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Courts/Show')
            ->where('court.court.name', 'Court 3')
            ->where('court.venueSlug', $court->venue->slug)
            ->where('court.averages.glass', 4.5)
            ->has('court.reviews.data', 10)
            ->where('court.reviews.meta.total', 12)
            ->where('sort', 'recent')
            ->has('sortOptions', 4)
            ->where('canReview', false));
});

it('sorts reviews as requested and keeps the sort in pagination links', function (): void {
    $court = Court::factory()->create();
    $low = Review::factory()->for($court)->create(['glass_rating' => 1, 'lighting_rating' => 1, 'turf_rating' => 1, 'facilities_rating' => 1]);
    $high = Review::factory()->for($court)->create(['glass_rating' => 5, 'lighting_rating' => 5, 'turf_rating' => 5, 'facilities_rating' => 5]);

    get(route('courts.show', [$court->venue, $court, 'sort' => 'highest']))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('sort', 'highest')
            ->where('court.reviews.data.0.id', $high->id)
            ->where('court.reviews.data.1.id', $low->id));

    get(route('courts.show', [$court->venue, $court, 'sort' => 'lowest']))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('court.reviews.data.0.id', $low->id));
});

it('tells a logged-in player whether they can review and whether they voted', function (): void {
    $player = User::factory()->player()->create();
    $court = Court::factory()->create();
    $review = Review::factory()->for($court)->create();
    ReviewVote::factory()->for($review)->for($player)->create();

    actingAs($player)
        ->get(route('courts.show', [$court->venue, $court]))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('canReview', true)
            ->where('court.reviews.data.0.hasVoted', true));

    actingAs($review->user)
        ->get(route('courts.show', [$court->venue, $court]))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('canReview', false)
            ->where('court.reviews.data.0.hasVoted', false));
});

it('404s when the court belongs to a different venue', function (): void {
    $court = Court::factory()->create();
    $otherVenue = Venue::factory()->create();

    get(route('courts.show', [$otherVenue, $court]))->assertNotFound();
});

it('404s for an unknown court slug', function (): void {
    $venue = Venue::factory()->create();

    get(route('courts.show', [$venue, 'court-99']))->assertNotFound();
});

it('rejects an invalid sort', function (): void {
    $court = Court::factory()->create();

    get(route('courts.show', [$court->venue, $court, 'sort' => 'random']))
        ->assertRedirect()
        ->assertSessionHasErrors('sort');
});
