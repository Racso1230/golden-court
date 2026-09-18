<?php

declare(strict_types=1);

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;

describe('create', function (): void {
    it('lets a player review a court they have not reviewed', function (): void {
        $player = User::factory()->player()->create();
        $court = Court::factory()->create();

        expect($player->can('create', [Review::class, $court]))->toBeTrue();
    });

    it('lets an admin review too', function (): void {
        $admin = User::factory()->admin()->create();

        expect($admin->can('create', [Review::class, Court::factory()->create()]))->toBeTrue();
    });

    it('stops a venue owner reviewing', function (): void {
        $owner = User::factory()->venueOwner()->create();

        expect($owner->can('create', [Review::class, Court::factory()->create()]))->toBeFalse();
    });

    it('stops a player reviewing the same court twice', function (): void {
        $review = Review::factory()->for(User::factory()->player())->create();

        expect($review->user->can('create', [Review::class, $review->court]))->toBeFalse()
            ->and($review->user->can('create', [Review::class, Court::factory()->create()]))->toBeTrue();
    });
});

describe('update', function (): void {
    it('lets the owner edit a pending or published review', function (): void {
        $pending = Review::factory()->pending()->create();
        $published = Review::factory()->create();

        expect($pending->user->can('update', $pending))->toBeTrue()
            ->and($published->user->can('update', $published))->toBeTrue();
    });

    it('stops the owner editing a flagged or removed review', function (): void {
        $flagged = Review::factory()->flagged()->create();
        $removed = Review::factory()->removed()->create();

        expect($flagged->user->can('update', $flagged))->toBeFalse()
            ->and($removed->user->can('update', $removed))->toBeFalse();
    });

    it('stops anyone else editing', function (): void {
        $review = Review::factory()->create();

        expect(User::factory()->player()->create()->can('update', $review))->toBeFalse();
    });

    it('lets an admin edit anything', function (): void {
        $admin = User::factory()->admin()->create();

        expect($admin->can('update', Review::factory()->removed()->create()))->toBeTrue();
    });
});

describe('delete', function (): void {
    it('lets the owner or an admin delete', function (): void {
        $review = Review::factory()->create();

        expect($review->user->can('delete', $review))->toBeTrue()
            ->and(User::factory()->admin()->create()->can('delete', $review))->toBeTrue()
            ->and(User::factory()->player()->create()->can('delete', $review))->toBeFalse();
    });
});

describe('changeStatus', function (): void {
    it('is admin only', function (): void {
        $review = Review::factory()->create();

        expect(User::factory()->admin()->create()->can('changeStatus', $review))->toBeTrue()
            ->and($review->user->can('changeStatus', $review))->toBeFalse()
            ->and(User::factory()->venueOwner()->create()->can('changeStatus', $review))->toBeFalse();
    });
});
