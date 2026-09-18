<?php

declare(strict_types=1);

use App\Domain\Claims\Enums\ClaimStatus;
use App\Domain\Claims\Models\VenueClaim;
use App\Domain\Users\Models\User;
use App\Domain\Venues\Models\Venue;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

const CLAIM_EVIDENCE = 'I am the club manager; my email matches the domain on our website.';

it('submits a pending claim and returns to the venue', function (): void {
    $venue = Venue::factory()->create();
    $user = User::factory()->player()->create();

    actingAs($user)
        ->post(route('venues.claims.store', $venue), ['evidence' => CLAIM_EVIDENCE])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('venues.show', $venue));

    $claim = VenueClaim::query()->sole();

    expect($claim->status)->toBe(ClaimStatus::Pending)
        ->and($claim->user_id)->toBe($user->id)
        ->and($claim->venue_id)->toBe($venue->id);
});

it('validates the evidence', function (): void {
    actingAs(User::factory()->player()->create())
        ->post(route('venues.claims.store', Venue::factory()->create()), ['evidence' => 'too short'])
        ->assertSessionHasErrors('evidence');
});

it('forbids claiming a venue that already has an owner', function (): void {
    $venue = Venue::factory()->claimedBy(User::factory()->venueOwner()->create())->create();

    actingAs(User::factory()->player()->create())
        ->post(route('venues.claims.store', $venue), ['evidence' => CLAIM_EVIDENCE])
        ->assertForbidden();
});

it('reports a competing pending claim as a validation error', function (): void {
    $venue = Venue::factory()->create();
    VenueClaim::factory()->for($venue)->create();

    actingAs(User::factory()->player()->create())
        ->post(route('venues.claims.store', $venue), ['evidence' => CLAIM_EVIDENCE])
        ->assertSessionHasErrors('evidence');

    expect(VenueClaim::query()->count())->toBe(1);
});

it('redirects guests to the login page', function (): void {
    post(route('venues.claims.store', Venue::factory()->create()), ['evidence' => CLAIM_EVIDENCE])
        ->assertRedirect(route('login'));
});

it('tells the venue page whether the viewer may claim', function (): void {
    $venue = Venue::factory()->create();

    get(route('venues.show', $venue))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->where('canClaim', false));

    actingAs(User::factory()->player()->create())
        ->get(route('venues.show', $venue))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->where('canClaim', true));
});
