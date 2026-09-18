<?php

declare(strict_types=1);

use App\Domain\Claims\Actions\SubmitVenueClaimAction;
use App\Domain\Claims\Data\SubmitVenueClaimData;
use App\Domain\Claims\Enums\ClaimStatus;
use App\Domain\Claims\Exceptions\ClaimAlreadyPendingException;
use App\Domain\Claims\Exceptions\VenueAlreadyClaimedException;
use App\Domain\Claims\Models\VenueClaim;
use App\Domain\Users\Models\User;
use App\Domain\Venues\Models\Venue;

function claimData(): SubmitVenueClaimData
{
    return new SubmitVenueClaimData(evidence: 'I manage the club; my email matches the domain on our website.');
}

it('creates a pending claim', function (): void {
    $venue = Venue::factory()->create();
    $user = User::factory()->player()->create();

    $claim = app(SubmitVenueClaimAction::class)->handle($user, $venue, claimData());

    expect($claim->status)->toBe(ClaimStatus::Pending)
        ->and($claim->venue_id)->toBe($venue->id)
        ->and($claim->user_id)->toBe($user->id)
        ->and($claim->evidence)->toStartWith('I manage the club');
});

it('refuses a claim on a venue that already has an owner', function (): void {
    $venue = Venue::factory()->claimedBy(User::factory()->venueOwner()->create())->create();

    expect(fn () => app(SubmitVenueClaimAction::class)->handle(User::factory()->player()->create(), $venue, claimData()))
        ->toThrow(VenueAlreadyClaimedException::class);

    expect(VenueClaim::query()->count())->toBe(0);
});

it('translates the one-pending-claim index into a domain exception', function (): void {
    $venue = Venue::factory()->create();
    VenueClaim::factory()->for($venue)->create();

    expect(fn () => app(SubmitVenueClaimAction::class)->handle(User::factory()->player()->create(), $venue, claimData()))
        ->toThrow(ClaimAlreadyPendingException::class);

    expect(VenueClaim::query()->count())->toBe(1);
});

it('allows a new claim once the previous one was rejected', function (): void {
    $venue = Venue::factory()->create();
    VenueClaim::factory()->for($venue)->rejected()->create();

    $claim = app(SubmitVenueClaimAction::class)->handle(User::factory()->player()->create(), $venue, claimData());

    expect($claim->exists)->toBeTrue()
        ->and(VenueClaim::query()->where('venue_id', $venue->id)->count())->toBe(2);
});
