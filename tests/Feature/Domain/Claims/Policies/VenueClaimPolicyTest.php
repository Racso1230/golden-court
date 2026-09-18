<?php

declare(strict_types=1);

use App\Domain\Claims\Models\VenueClaim;
use App\Domain\Users\Models\User;
use App\Domain\Venues\Models\Venue;

it('lets any signed-in user claim an unclaimed venue', function (): void {
    $venue = Venue::factory()->create();

    expect(User::factory()->player()->create()->can('create', [VenueClaim::class, $venue]))->toBeTrue()
        ->and(User::factory()->venueOwner()->create()->can('create', [VenueClaim::class, $venue]))->toBeTrue();
});

it('stops anyone claiming a venue that already has an owner', function (): void {
    $venue = Venue::factory()->claimedBy(User::factory()->venueOwner()->create())->create();

    expect(User::factory()->player()->create()->can('create', [VenueClaim::class, $venue]))->toBeFalse();
});

it('lets only admins review claims', function (): void {
    $claim = VenueClaim::factory()->create();

    expect(User::factory()->admin()->create()->can('review', $claim))->toBeTrue()
        ->and(User::factory()->player()->create()->can('review', $claim))->toBeFalse()
        ->and($claim->user->can('review', $claim))->toBeFalse();
});
