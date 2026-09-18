<?php

declare(strict_types=1);

use App\Domain\Claims\Actions\ReviewVenueClaimAction;
use App\Domain\Claims\Enums\ClaimStatus;
use App\Domain\Claims\Events\VenueClaimApproved;
use App\Domain\Claims\Events\VenueClaimRejected;
use App\Domain\Claims\Exceptions\InvalidClaimTransitionException;
use App\Domain\Claims\Exceptions\VenueAlreadyClaimedException;
use App\Domain\Claims\Models\VenueClaim;
use App\Domain\Reviews\Exceptions\ModerationNotPermittedException;
use App\Domain\Users\Enums\Role;
use App\Domain\Users\Models\User;
use App\Domain\Venues\Models\Venue;
use Illuminate\Support\Facades\Event;

it('approves a claim, hands over the venue and promotes the claimant', function (): void {
    Event::fake([VenueClaimApproved::class, VenueClaimRejected::class]);
    $admin = User::factory()->admin()->create();
    $claim = VenueClaim::factory()->for(User::factory()->player())->create();

    $decided = app(ReviewVenueClaimAction::class)->handle($claim, $admin, ClaimStatus::Approved);

    expect($decided->refresh()->status)->toBe(ClaimStatus::Approved)
        ->and($decided->reviewed_by_user_id)->toBe($admin->id)
        ->and($decided->reviewed_at)->not->toBeNull()
        ->and($decided->rejection_reason)->toBeNull()
        ->and($claim->venue->refresh()->claimed_by_user_id)->toBe($claim->user_id)
        ->and($claim->user->refresh()->role)->toBe(Role::VenueOwner);

    Event::assertDispatched(VenueClaimApproved::class, fn (VenueClaimApproved $event): bool => $event->claimId === $claim->id && $event->userId === $claim->user_id);
    Event::assertNotDispatched(VenueClaimRejected::class);
});

it('does not demote an admin who claims a venue', function (): void {
    Event::fake([VenueClaimApproved::class]);
    $claimant = User::factory()->admin()->create();
    $claim = VenueClaim::factory()->for($claimant)->create();

    app(ReviewVenueClaimAction::class)->handle($claim, User::factory()->admin()->create(), ClaimStatus::Approved);

    expect($claimant->refresh()->role)->toBe(Role::Admin);
});

it('rejects a claim with a reason and leaves the venue and role alone', function (): void {
    Event::fake([VenueClaimApproved::class, VenueClaimRejected::class]);
    $admin = User::factory()->admin()->create();
    $claim = VenueClaim::factory()->for(User::factory()->player())->create();

    app(ReviewVenueClaimAction::class)->handle($claim, $admin, ClaimStatus::Rejected, 'We could not verify you.');

    expect($claim->refresh()->status)->toBe(ClaimStatus::Rejected)
        ->and($claim->rejection_reason)->toBe('We could not verify you.')
        ->and($claim->venue->refresh()->isClaimed())->toBeFalse()
        ->and($claim->user->refresh()->role)->toBe(Role::Player);

    Event::assertDispatched(VenueClaimRejected::class, fn (VenueClaimRejected $event): bool => $event->rejectionReason === 'We could not verify you.');
});

it('refuses to decide a claim twice', function (): void {
    $admin = User::factory()->admin()->create();
    $claim = VenueClaim::factory()->approved()->create();

    expect(fn () => app(ReviewVenueClaimAction::class)->handle($claim, $admin, ClaimStatus::Rejected))
        ->toThrow(InvalidClaimTransitionException::class);
});

it('refuses to set a claim back to pending', function (): void {
    $admin = User::factory()->admin()->create();
    $claim = VenueClaim::factory()->create();

    expect(fn () => app(ReviewVenueClaimAction::class)->handle($claim, $admin, ClaimStatus::Pending))
        ->toThrow(InvalidClaimTransitionException::class);
});

it('refuses non-admins', function (): void {
    $claim = VenueClaim::factory()->create();

    expect(fn () => app(ReviewVenueClaimAction::class)->handle($claim, User::factory()->venueOwner()->create(), ClaimStatus::Approved))
        ->toThrow(ModerationNotPermittedException::class);

    expect($claim->refresh()->status)->toBe(ClaimStatus::Pending);
});

it('refuses to approve when the venue gained an owner in the meantime', function (): void {
    $admin = User::factory()->admin()->create();
    $claim = VenueClaim::factory()->create();
    Venue::query()->whereKey($claim->venue_id)->toBase()->update(['claimed_by_user_id' => User::factory()->venueOwner()->create()->id]);

    expect(fn () => app(ReviewVenueClaimAction::class)->handle($claim, $admin, ClaimStatus::Approved))
        ->toThrow(VenueAlreadyClaimedException::class);

    expect($claim->refresh()->status)->toBe(ClaimStatus::Pending);
});
