<?php

declare(strict_types=1);

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Models\ReviewReply;
use App\Domain\Users\Models\User;
use App\Domain\Venues\Models\Venue;

function reviewAtOwnedVenue(User $owner): Review
{
    $venue = Venue::factory()->claimedBy($owner)->create();

    return Review::factory()->for(Court::factory()->for($venue))->create();
}

describe('create', function (): void {
    it('lets the owner of the venue reply to a published review there', function (): void {
        $owner = User::factory()->venueOwner()->create();

        expect($owner->can('create', [ReviewReply::class, reviewAtOwnedVenue($owner)]))->toBeTrue();
    });

    it('forbids the owner of a different venue', function (): void {
        $owner = User::factory()->venueOwner()->create();
        $otherOwner = User::factory()->venueOwner()->create();

        expect($otherOwner->can('create', [ReviewReply::class, reviewAtOwnedVenue($owner)]))->toBeFalse();
    });

    it('forbids players, including the review author', function (): void {
        $review = reviewAtOwnedVenue(User::factory()->venueOwner()->create());

        expect(User::factory()->player()->create()->can('create', [ReviewReply::class, $review]))->toBeFalse()
            ->and($review->user->can('create', [ReviewReply::class, $review]))->toBeFalse();
    });

    it('lets an admin reply anywhere', function (): void {
        $review = reviewAtOwnedVenue(User::factory()->venueOwner()->create());

        expect(User::factory()->admin()->create()->can('create', [ReviewReply::class, $review]))->toBeTrue();
    });

    it('forbids replying to a review that is not published', function (): void {
        $owner = User::factory()->venueOwner()->create();
        $venue = Venue::factory()->claimedBy($owner)->create();
        $pending = Review::factory()->for(Court::factory()->for($venue))->pending()->create();

        expect($owner->can('create', [ReviewReply::class, $pending]))->toBeFalse();
    });
});

describe('update and delete', function (): void {
    it('lets the reply author or an admin edit and delete', function (): void {
        $reply = ReviewReply::factory()->create();
        $stranger = User::factory()->venueOwner()->create();
        $admin = User::factory()->admin()->create();

        expect($reply->user->can('update', $reply))->toBeTrue()
            ->and($reply->user->can('delete', $reply))->toBeTrue()
            ->and($admin->can('update', $reply))->toBeTrue()
            ->and($admin->can('delete', $reply))->toBeTrue()
            ->and($stranger->can('update', $reply))->toBeFalse()
            ->and($stranger->can('delete', $reply))->toBeFalse();
    });
});
