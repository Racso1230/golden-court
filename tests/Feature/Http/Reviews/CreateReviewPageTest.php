<?php

declare(strict_types=1);

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests to the login page', function (): void {
    $court = Court::factory()->create();

    get(route('reviews.create', $court))->assertRedirect(route('login'));
});

it('sends unverified users to verify their email first', function (): void {
    $court = Court::factory()->create();

    actingAs(User::factory()->player()->unverified()->create())
        ->get(route('reviews.create', $court))
        ->assertRedirect(route('verification.notice'));
});

it('renders the form with the court and venue summary', function (): void {
    $court = Court::factory()->create(['name' => 'Court 3']);

    actingAs(User::factory()->player()->create())
        ->get(route('reviews.create', $court))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Reviews/Create')
            ->where('court.id', $court->id)
            ->where('court.name', 'Court 3')
            ->where('court.venue.name', $court->venue->name)
            ->where('court.venue.city', $court->venue->city));
});

it('forbids venue owners', function (): void {
    $court = Court::factory()->create();

    actingAs(User::factory()->venueOwner()->create())
        ->get(route('reviews.create', $court))
        ->assertForbidden();
});

it('forbids a player who has already reviewed the court', function (): void {
    $review = Review::factory()->for(User::factory()->player())->create();

    actingAs($review->user)
        ->get(route('reviews.create', $review->court))
        ->assertForbidden();
});
