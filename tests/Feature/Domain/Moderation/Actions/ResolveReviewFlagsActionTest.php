<?php

declare(strict_types=1);

use App\Domain\Moderation\Actions\ResolveReviewFlagsAction;
use App\Domain\Moderation\Exceptions\InvalidFlagOutcomeException;
use App\Domain\Moderation\Models\ReviewFlag;
use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Reviews\Events\ReviewStatusChanged;
use App\Domain\Reviews\Exceptions\ModerationNotPermittedException;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;
use Illuminate\Support\Facades\Event;

it('publishes a flagged review and resolves every open flag', function (): void {
    Event::fake([ReviewStatusChanged::class]);
    $admin = User::factory()->admin()->create();
    $review = Review::factory()->flagged()->create();
    ReviewFlag::factory()->count(3)->for($review)->create();

    app(ResolveReviewFlagsAction::class)->handle($review, $admin, ReviewStatus::Published);

    expect($review->refresh()->status)->toBe(ReviewStatus::Published)
        ->and($review->flags()->whereNull('resolved_at')->count())->toBe(0)
        ->and($review->flags()->where('resolved_by_user_id', $admin->id)->count())->toBe(3);

    Event::assertDispatched(ReviewStatusChanged::class, fn (ReviewStatusChanged $event): bool => $event->to === ReviewStatus::Published);
});

it('removes a flagged review', function (): void {
    Event::fake([ReviewStatusChanged::class]);
    $review = Review::factory()->flagged()->create();
    ReviewFlag::factory()->for($review)->create();

    app(ResolveReviewFlagsAction::class)->handle($review, User::factory()->admin()->create(), ReviewStatus::Removed);

    expect($review->refresh()->status)->toBe(ReviewStatus::Removed)
        ->and($review->flags()->whereNull('resolved_at')->count())->toBe(0);
});

it('dismisses flags on a still-published review without changing its status', function (): void {
    Event::fake([ReviewStatusChanged::class]);
    $review = Review::factory()->create();
    ReviewFlag::factory()->for($review)->create();

    app(ResolveReviewFlagsAction::class)->handle($review, User::factory()->admin()->create(), ReviewStatus::Published);

    expect($review->refresh()->status)->toBe(ReviewStatus::Published)
        ->and($review->flags()->whereNull('resolved_at')->count())->toBe(0);
    Event::assertNotDispatched(ReviewStatusChanged::class);
});

it('only accepts published or removed as an outcome', function (): void {
    $review = Review::factory()->flagged()->create();

    expect(fn () => app(ResolveReviewFlagsAction::class)->handle($review, User::factory()->admin()->create(), ReviewStatus::Pending))
        ->toThrow(InvalidFlagOutcomeException::class);
});

it('refuses non-admins', function (): void {
    $review = Review::factory()->flagged()->create();
    ReviewFlag::factory()->for($review)->create();

    expect(fn () => app(ResolveReviewFlagsAction::class)->handle($review, User::factory()->player()->create(), ReviewStatus::Published))
        ->toThrow(ModerationNotPermittedException::class);

    expect($review->flags()->whereNull('resolved_at')->count())->toBe(1);
});
