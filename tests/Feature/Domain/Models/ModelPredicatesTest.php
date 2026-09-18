<?php

declare(strict_types=1);

use App\Domain\Claims\Models\VenueClaim;
use App\Domain\Courts\Models\Court;
use App\Domain\Moderation\Models\ReviewFlag;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;
use App\Domain\Venues\Models\Venue;

it('knows which users are admins and venue owners', function (): void {
    $admin = User::factory()->admin()->create();
    $owner = User::factory()->venueOwner()->create();
    $player = User::factory()->player()->create();

    expect($admin->isAdmin())->toBeTrue()
        ->and($admin->isVenueOwner())->toBeFalse()
        ->and($owner->isVenueOwner())->toBeTrue()
        ->and($owner->isAdmin())->toBeFalse()
        ->and($player->isAdmin())->toBeFalse()
        ->and($player->isVenueOwner())->toBeFalse();
});

it('knows whether a venue has been claimed', function (): void {
    $owner = User::factory()->venueOwner()->create();

    expect(Venue::factory()->create()->isClaimed())->toBeFalse()
        ->and(Venue::factory()->claimedBy($owner)->create()->isClaimed())->toBeTrue()
        ->and(Venue::factory()->claimedBy($owner)->create()->owner?->is($owner))->toBeTrue();
});

it('lists only published reviews through the published scope and relationship', function (): void {
    $court = Court::factory()->create();
    Review::factory()->for($court)->create();
    Review::factory()->for($court)->pending()->create();
    Review::factory()->for($court)->flagged()->create();
    Review::factory()->for($court)->removed()->create();

    expect(Review::query()->published()->count())->toBe(1)
        ->and($court->publishedReviews()->count())->toBe(1)
        ->and($court->reviews()->count())->toBe(4);
});

it('reports review visibility from its status', function (): void {
    expect(Review::factory()->create()->isPublished())->toBeTrue()
        ->and(Review::factory()->pending()->create()->isPublished())->toBeFalse();
});

it('reports whether a flag has been resolved', function (): void {
    expect(ReviewFlag::factory()->create()->isResolved())->toBeFalse()
        ->and(ReviewFlag::factory()->resolved()->create()->isResolved())->toBeTrue();
});

it('reports whether a claim is still pending', function (): void {
    expect(VenueClaim::factory()->create()->isPending())->toBeTrue()
        ->and(VenueClaim::factory()->approved()->create()->isPending())->toBeFalse();
});
