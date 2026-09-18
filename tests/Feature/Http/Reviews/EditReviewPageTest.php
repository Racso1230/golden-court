<?php

declare(strict_types=1);

use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('renders the edit form for the review owner', function (): void {
    $review = Review::factory()->for(User::factory()->player())->create(['glass_rating' => 4]);

    actingAs($review->user)
        ->get(route('reviews.edit', $review))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Reviews/Edit')
            ->where('review.id', $review->id)
            ->where('review.scores.glass', 4)
            ->where('review.isAuthor', true)
            ->where('court.id', $review->court_id)
            ->where('venueName', $review->court->venue->name)
            ->where('venueSlug', $review->court->venue->slug));
});

it('forbids other players', function (): void {
    $review = Review::factory()->create();

    actingAs(User::factory()->player()->create())
        ->get(route('reviews.edit', $review))
        ->assertForbidden();
});

it('forbids the owner once the review is flagged', function (): void {
    $review = Review::factory()->flagged()->create();

    actingAs($review->user)->get(route('reviews.edit', $review))->assertForbidden();
});

it('redirects guests to the login page', function (): void {
    get(route('reviews.edit', Review::factory()->create()))->assertRedirect(route('login'));
});
