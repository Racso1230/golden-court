<?php

declare(strict_types=1);

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Models\ReviewVote;
use App\Domain\Users\Models\User;
use App\Domain\Venues\Models\Venue;
use Illuminate\Support\Collection;
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
            ->has('reviews.data', 10)
            ->where('reviews.total', 12)
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
            ->where('reviews.data.0.id', $high->id)
            ->where('reviews.data.1.id', $low->id));

    get(route('courts.show', [$court->venue, $court, 'sort' => 'lowest']))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('reviews.data.0.id', $low->id));
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
            ->where('reviews.data.0.hasVoted', true));

    actingAs($review->user)
        ->get(route('courts.show', [$court->venue, $court]))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('canReview', false)
            ->where('reviews.data.0.hasVoted', false));
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

it('places the court at its venue', function (): void {
    $court = Court::factory()->create();
    $venue = $court->venue;

    get(route('courts.show', [$venue, $court]))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('court.venueAddress.line1', $venue->address_line_1)
            ->where('court.venueAddress.city', $venue->city)
            ->where('court.venueAddress.postcode', $venue->postcode)
            ->where('court.venueCoordinates.latitude', $venue->latitude)
            ->where('court.venueCoordinates.longitude', $venue->longitude)
            ->where('court.venueWebsite', $venue->website));
});

it('describes the court for search engines with its reviews as structured data', function (): void {
    $court = Court::factory()->create(['name' => 'Court 3']);
    Review::factory()->count(2)->for($court)->create();

    get(route('courts.show', [$court->venue, $court]))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('head', function (Collection $head) use ($court): bool {
                $tags = $head->all();
                $place = jsonLd($tags, 'court');

                return str_contains((string) headTag($tags, 'title'), sprintf('Court 3 at %s', $court->venue->name))
                    && headTag($tags, 'robots') === '<meta name="robots" content="index, follow" data-inertia="robots">'
                    && ($place['containedInPlace']['name'] ?? null) === $court->venue->name
                    && count($place['review'] ?? []) === 2;
            }));
});

it('keeps re-sorted review lists out of the index', function (): void {
    $court = Court::factory()->create();

    get(route('courts.show', [$court->venue, $court, 'sort' => 'highest']))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('head', fn (Collection $head): bool => headTag($head->all(), 'robots') === '<meta name="robots" content="noindex, follow" data-inertia="robots">'));
});
