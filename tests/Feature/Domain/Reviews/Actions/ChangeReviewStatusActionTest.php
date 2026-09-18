<?php

declare(strict_types=1);

use App\Domain\Moderation\SystemActor;
use App\Domain\Reviews\Actions\ChangeReviewStatusAction;
use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Reviews\Events\ReviewStatusChanged;
use App\Domain\Reviews\Exceptions\InvalidStatusTransitionException;
use App\Domain\Reviews\Exceptions\ModerationNotPermittedException;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;
use Illuminate\Support\Facades\Event;

it('moves a review along an allowed transition and records the change', function (): void {
    Event::fake([ReviewStatusChanged::class]);
    $admin = User::factory()->admin()->create();
    $review = Review::factory()->pending()->create();

    $updated = app(ChangeReviewStatusAction::class)->handle($review, ReviewStatus::Published, $admin);

    expect($updated->refresh()->status)->toBe(ReviewStatus::Published);

    Event::assertDispatched(
        ReviewStatusChanged::class,
        fn (ReviewStatusChanged $event): bool => $event->reviewId === $review->id
            && $event->courtId === $review->court_id
            && $event->from === ReviewStatus::Pending
            && $event->to === ReviewStatus::Published,
    );
});

it('rejects a transition the status machine forbids', function (): void {
    Event::fake([ReviewStatusChanged::class]);
    $admin = User::factory()->admin()->create();
    $review = Review::factory()->create();

    expect(fn () => app(ChangeReviewStatusAction::class)->handle($review, ReviewStatus::Pending, $admin))
        ->toThrow(InvalidStatusTransitionException::class);

    expect($review->refresh()->status)->toBe(ReviewStatus::Published);
    Event::assertNotDispatched(ReviewStatusChanged::class);
});

it('refuses to let a non-admin moderate', function (): void {
    Event::fake([ReviewStatusChanged::class]);
    $player = User::factory()->player()->create();
    $review = Review::factory()->pending()->create();

    expect(fn () => app(ChangeReviewStatusAction::class)->handle($review, ReviewStatus::Published, $player))
        ->toThrow(ModerationNotPermittedException::class);

    expect($review->refresh()->status)->toBe(ReviewStatus::Pending);
});

it('lets the system actor moderate, for automatic escalation', function (): void {
    Event::fake([ReviewStatusChanged::class]);
    $review = Review::factory()->create();

    app(ChangeReviewStatusAction::class)->handle($review, ReviewStatus::Flagged, new SystemActor);

    expect($review->refresh()->status)->toBe(ReviewStatus::Flagged);
});
