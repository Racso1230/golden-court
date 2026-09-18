<?php

declare(strict_types=1);

use App\Domain\Reviews\Contracts\ReviewPublicationRule;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\PublicationRules\VerifiedUserAutoPublishRule;
use App\Domain\Users\Models\User;

it('is the publication rule bound in the container', function (): void {
    expect(app(ReviewPublicationRule::class))->toBeInstanceOf(VerifiedUserAutoPublishRule::class);
});

it('publishes immediately for a verified user with a clean record', function (): void {
    $user = User::factory()->player()->create();
    $review = Review::factory()->for($user)->pending()->create();

    expect((new VerifiedUserAutoPublishRule)->shouldAutoPublish($user, $review))->toBeTrue();
});

it('holds reviews from users with an unverified email', function (): void {
    $user = User::factory()->player()->unverified()->create();
    $review = Review::factory()->for($user)->pending()->create();

    expect((new VerifiedUserAutoPublishRule)->shouldAutoPublish($user, $review))->toBeFalse();
});

it('holds reviews from users who have had a review removed', function (): void {
    $user = User::factory()->player()->create();
    Review::factory()->for($user)->removed()->create();
    $review = Review::factory()->for($user)->pending()->create();

    expect((new VerifiedUserAutoPublishRule)->shouldAutoPublish($user, $review))->toBeFalse();
});

it('still counts a removed review that was later soft deleted', function (): void {
    $user = User::factory()->player()->create();
    Review::factory()->for($user)->removed()->create()->delete();
    $review = Review::factory()->for($user)->pending()->create();

    expect((new VerifiedUserAutoPublishRule)->shouldAutoPublish($user, $review))->toBeFalse();
});
