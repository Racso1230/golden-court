<?php

declare(strict_types=1);

use App\Domain\Claims\Actions\ReviewVenueClaimAction;
use App\Domain\Claims\Enums\ClaimStatus;
use App\Domain\Claims\Models\VenueClaim;
use App\Domain\Claims\Notifications\VenueClaimApprovedNotification;
use App\Domain\Claims\Notifications\VenueClaimRejectedNotification;
use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Actions\ChangeReviewStatusAction;
use App\Domain\Reviews\Actions\ReplyToReviewAction;
use App\Domain\Reviews\Actions\SubmitReviewAction;
use App\Domain\Reviews\Data\ReplyToReviewData;
use App\Domain\Reviews\Data\SubmitReviewData;
use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Notifications\ReviewOutcomeNotification;
use App\Domain\Reviews\Notifications\ReviewRepliedNotification;
use App\Domain\Users\Models\User;
use Illuminate\Support\Facades\Notification;

it('notifies the claimant when their claim is approved', function (): void {
    Notification::fake();
    $claim = VenueClaim::factory()->for(User::factory()->player())->create();

    app(ReviewVenueClaimAction::class)->handle($claim, User::factory()->admin()->create(), ClaimStatus::Approved);

    Notification::assertSentTo(
        $claim->user,
        VenueClaimApprovedNotification::class,
        fn (VenueClaimApprovedNotification $notification): bool => $notification->venueId === $claim->venue_id
            && $notification->toArray($claim->user)['venue_slug'] === $claim->venue->slug,
    );
});

it('notifies the claimant with the reason when their claim is rejected', function (): void {
    Notification::fake();
    $claim = VenueClaim::factory()->create();

    app(ReviewVenueClaimAction::class)->handle($claim, User::factory()->admin()->create(), ClaimStatus::Rejected, 'No proof of ownership.');

    Notification::assertSentTo(
        $claim->user,
        VenueClaimRejectedNotification::class,
        fn (VenueClaimRejectedNotification $notification): bool => $notification->rejectionReason === 'No proof of ownership.',
    );
});

it('notifies the author when their review is published or removed, but not when flagged', function (): void {
    Notification::fake();
    $admin = User::factory()->admin()->create();
    $pending = Review::factory()->pending()->create();
    $published = Review::factory()->create();

    app(ChangeReviewStatusAction::class)->handle($pending, ReviewStatus::Published, $admin);
    app(ChangeReviewStatusAction::class)->handle($published, ReviewStatus::Flagged, $admin);

    Notification::assertSentTo($pending->user, ReviewOutcomeNotification::class, fn (ReviewOutcomeNotification $n): bool => $n->outcome === ReviewStatus::Published);
    Notification::assertNotSentTo($published->user, ReviewOutcomeNotification::class);

    app(ChangeReviewStatusAction::class)->handle($published, ReviewStatus::Removed, $admin);

    Notification::assertSentTo($published->user, ReviewOutcomeNotification::class, fn (ReviewOutcomeNotification $n): bool => $n->outcome === ReviewStatus::Removed);
});

it('notifies the author when a review is auto-published on submission', function (): void {
    Notification::fake();
    $player = User::factory()->player()->create();
    $court = Court::factory()->create();

    app(SubmitReviewAction::class)->handle($player, new SubmitReviewData($court->id, 4, 4, 4, 4, 'Smooth glass, good lights, friendly staff at the desk.'));

    Notification::assertSentTo($player, ReviewOutcomeNotification::class);
});

it('notifies the author when the venue replies', function (): void {
    Notification::fake();
    $review = Review::factory()->create();
    $owner = User::factory()->venueOwner()->create();

    $reply = app(ReplyToReviewAction::class)->handle($owner, $review, new ReplyToReviewData('Thanks for coming!'));

    Notification::assertSentTo(
        $review->user,
        ReviewRepliedNotification::class,
        fn (ReviewRepliedNotification $notification): bool => $notification->replyId === $reply->id && $notification->venueName === $review->court->venue->name,
    );
});

it('stores notifications in the database', function (): void {
    $review = Review::factory()->create();

    app(ReplyToReviewAction::class)->handle(User::factory()->venueOwner()->create(), $review, new ReplyToReviewData('Thanks!'));

    expect($review->user->notifications()->count())->toBe(1)
        ->and($review->user->notifications()->first()?->data['message'])->toContain('replied to your review');
});
