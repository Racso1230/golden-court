<?php

declare(strict_types=1);

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Enums\Role;
use App\Domain\Users\Models\User;
use App\Domain\Venues\Models\Venue;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;

it('takes a player from claiming a venue to replying to a review on it', function (): void {
    $venue = Venue::factory()->create();
    $court = Court::factory()->for($venue)->create();
    $review = Review::factory()->for($court)->create();
    $claimant = User::factory()->player()->create();
    $admin = User::factory()->admin()->create();

    // Before the claim, the player cannot reply.
    actingAs($claimant)
        ->post(route('reviews.reply.store', $review), ['body' => 'Not yet allowed.'])
        ->assertForbidden();

    // The player claims the venue.
    actingAs($claimant)
        ->post(route('venues.claims.store', $venue), ['evidence' => 'I am the club manager; my email matches the domain on our website.'])
        ->assertSessionHasNoErrors();

    $claim = $venue->refresh()->claims()->sole();

    // An admin approves it from the admin area.
    actingAs($admin)
        ->post(route('admin.claims.approve', $claim))
        ->assertSessionHasNoErrors();

    expect($venue->refresh()->claimed_by_user_id)->toBe($claimant->id)
        ->and($claimant->refresh()->role)->toBe(Role::VenueOwner);

    // The court page now offers the reply form and the reply goes through.
    actingAs($claimant)
        ->get(route('courts.show', [$venue, $court]))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->where('canReply', true));

    actingAs($claimant)
        ->post(route('reviews.reply.store', $review), ['body' => 'Thanks for visiting, see you on court.'])
        ->assertSessionHasNoErrors();

    actingAs($claimant)
        ->get(route('courts.show', [$venue, $court]))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('reviews.data.0.reply.body', 'Thanks for visiting, see you on court.')
            ->where('reviews.data.0.reply.authorDisplayName', $claimant->display_name)
            ->where('reviews.data.0.reply.fromOwner', true));
});
