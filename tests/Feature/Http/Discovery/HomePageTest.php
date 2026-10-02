<?php

declare(strict_types=1);

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Models\Review;
use App\Domain\Venues\Models\Venue;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\get;

it('renders the home page with top venues and recent reviews', function (): void {
    $ranked = Venue::factory()->create(['name' => 'Ranked Club']);
    Venue::query()->whereKey($ranked->id)->toBase()->update(['aggregate_score' => 4.6, 'review_count' => 12]);
    $tooFew = Venue::factory()->create(['name' => 'Quiet Club']);
    Venue::query()->whereKey($tooFew->id)->toBase()->update(['aggregate_score' => 5.0, 'review_count' => 9]);

    $court = Court::factory()->for($ranked)->create(['name' => 'Court 1']);
    $latest = Review::factory()->for($court)->create(['created_at' => '2026-09-15 10:00:00', 'body' => str_repeat('Superb glass and lighting. ', 10)]);
    Review::factory()->for($court)->create(['created_at' => '2026-09-01 10:00:00']);
    Review::factory()->for($court)->pending()->create(['created_at' => '2026-09-16 10:00:00']);

    get(route('home'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Home')
            ->has('topVenues', 1)
            ->where('topVenues.0.name', 'Ranked Club')
            ->where('topVenues.0.courtCount', 1)
            ->has('recentReviews', 2)
            ->where('recentReviews.0.id', $latest->id)
            ->where('recentReviews.0.venueSlug', $ranked->slug)
            ->where('recentReviews.0.courtSlug', $court->slug)
            ->where('recentReviews.0.excerpt', fn (mixed $excerpt): bool => is_string($excerpt) && mb_strlen($excerpt) <= 163));
});

it('renders with no data at all', function (): void {
    get(route('home'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Home')
            ->has('topVenues', 0)
            ->has('recentReviews', 0));
});

it('describes itself for search engines', function (): void {
    $base = rtrim((string) config('app.url'), '/');

    get(route('home'))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('head', fn (Collection $head): bool => headTag($head->all(), 'title') === sprintf('<title data-inertia="title">%s: padel court reviews and ratings</title>', config('app.name'))
                && headTag($head->all(), 'canonical') === sprintf('<link rel="canonical" href="%s/" data-inertia="canonical">', $base)
                && (jsonLd($head->all(), 'website')['@type'] ?? null) === 'WebSite'));
});
