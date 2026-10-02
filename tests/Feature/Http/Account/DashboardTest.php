<?php

declare(strict_types=1);

use App\Domain\Claims\Models\VenueClaim;
use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Models\ReviewReply;
use App\Domain\Users\Models\User;
use App\Domain\Venues\Models\Venue;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;

it('summarises a player\'s reviews and claims', function (): void {
    $player = User::factory()->player()->create();
    $published = Review::factory()->for($player)->create();
    Review::factory()->for($player)->pending()->create();
    DB::table('reviews')->where('id', $published->id)->update(['helpful_count' => 3]);
    VenueClaim::factory()->for($player)->create();

    actingAs($player)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Dashboard')
            ->where('summary.reviewCount', 2)
            ->where('summary.pendingReviewCount', 1)
            ->where('summary.helpfulVoteCount', 3)
            ->where('summary.claimCount', 1)
            ->where('summary.pendingClaimCount', 1)
            ->where('summary.ownedVenueCount', 0)
            ->where('summary.unansweredReviewCount', 0));
});

it('counts the reviews on an owner\'s venues that still need a reply', function (): void {
    $owner = User::factory()->venueOwner()->create();
    $court = Court::factory()->for(Venue::factory()->claimedBy($owner))->create();
    Review::factory()->count(2)->for($court)->create();
    $answered = Review::factory()->for($court)->create();
    ReviewReply::factory()->for($answered)->for($owner)->create();
    Review::factory()->for($court)->pending()->create();
    Review::factory()->create();

    actingAs($owner)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('summary.ownedVenueCount', 1)
            ->where('summary.unansweredReviewCount', 2));
});

it('shows zeros to a brand new account', function (): void {
    actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('summary.reviewCount', 0)
            ->where('summary.helpfulVoteCount', 0)
            ->where('summary.claimCount', 0));
});
