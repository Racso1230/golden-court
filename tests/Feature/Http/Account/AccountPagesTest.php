<?php

declare(strict_types=1);

use App\Domain\Claims\Models\VenueClaim;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('lists the signed-in user\'s reviews with status and edit permission', function (): void {
    $user = User::factory()->player()->create();
    $published = Review::factory()->for($user)->create(['created_at' => '2026-09-10 10:00:00']);
    $flagged = Review::factory()->for($user)->flagged()->create(['created_at' => '2026-09-12 10:00:00']);
    Review::factory()->create();

    actingAs($user)
        ->get(route('account.reviews'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Account/Reviews')
            ->has('reviews.data', 2)
            ->where('reviews.data.0.review.id', $flagged->id)
            ->where('reviews.data.0.statusLabel', 'Flagged')
            ->where('reviews.data.0.canEdit', false)
            ->where('reviews.data.1.review.id', $published->id)
            ->where('reviews.data.1.canEdit', true)
            ->where('reviews.data.1.venueSlug', $published->court->venue->slug));
});

it('lists the signed-in user\'s claims', function (): void {
    $user = User::factory()->player()->create();
    $claim = VenueClaim::factory()->for($user)->rejected()->create();
    VenueClaim::factory()->create();

    actingAs($user)
        ->get(route('account.claims'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Account/Claims')
            ->has('claims', 1)
            ->where('claims.0.id', $claim->id)
            ->where('claims.0.statusLabel', 'Rejected')
            ->where('claims.0.rejectionReason', $claim->rejection_reason));
});

it('redirects guests to the login page', function (): void {
    get(route('account.reviews'))->assertRedirect(route('login'));
    get(route('account.claims'))->assertRedirect(route('login'));
});
