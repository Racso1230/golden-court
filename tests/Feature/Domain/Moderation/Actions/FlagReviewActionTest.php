<?php

declare(strict_types=1);

use App\Domain\Moderation\Actions\FlagReviewAction;
use App\Domain\Moderation\Data\FlagReviewData;
use App\Domain\Moderation\Enums\FlagReason;
use App\Domain\Moderation\Exceptions\AlreadyFlaggedException;
use App\Domain\Moderation\Models\ReviewFlag;
use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Reviews\Events\ReviewStatusChanged;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;
use Illuminate\Support\Facades\Event;

function flagBy(User $user, Review $review, FlagReason $reason = FlagReason::Spam): ReviewFlag
{
    return app(FlagReviewAction::class)->handle($user, $review, new FlagReviewData($reason, 'Looks like an advert.'));
}

it('records the flag with its reason and details', function (): void {
    $review = Review::factory()->create();
    $reporter = User::factory()->player()->create();

    $flag = flagBy($reporter, $review, FlagReason::Offensive);

    expect($flag->exists)->toBeTrue()
        ->and($flag->reason)->toBe(FlagReason::Offensive)
        ->and($flag->details)->toBe('Looks like an advert.')
        ->and($flag->isResolved())->toBeFalse()
        ->and($review->refresh()->status)->toBe(ReviewStatus::Published);
});

it('refuses a second flag from the same user via the database', function (): void {
    $review = Review::factory()->create();
    $reporter = User::factory()->player()->create();
    flagBy($reporter, $review);

    expect(fn () => flagBy($reporter, $review))->toThrow(AlreadyFlaggedException::class);

    expect(ReviewFlag::query()->count())->toBe(1);
});

it('escalates a published review to flagged once the threshold is reached', function (): void {
    Event::fake([ReviewStatusChanged::class]);
    config()->set('golden_court.moderation.auto_flag_threshold', 3);
    $review = Review::factory()->create();

    foreach (User::factory()->player()->count(2)->create() as $reporter) {
        flagBy($reporter, $review);
    }

    expect($review->refresh()->status)->toBe(ReviewStatus::Published);

    flagBy(User::factory()->player()->create(), $review);

    expect($review->refresh()->status)->toBe(ReviewStatus::Flagged);
    Event::assertDispatched(
        ReviewStatusChanged::class,
        fn (ReviewStatusChanged $event): bool => $event->reviewId === $review->id
            && $event->from === ReviewStatus::Published
            && $event->to === ReviewStatus::Flagged,
    );
});

it('reads the threshold from configuration', function (): void {
    config()->set('golden_court.moderation.auto_flag_threshold', 1);
    $review = Review::factory()->create();

    flagBy(User::factory()->player()->create(), $review);

    expect($review->refresh()->status)->toBe(ReviewStatus::Flagged);
});

it('only counts unresolved flags towards the threshold', function (): void {
    config()->set('golden_court.moderation.auto_flag_threshold', 2);
    $review = Review::factory()->create();
    ReviewFlag::factory()->for($review)->resolved()->create();

    flagBy(User::factory()->player()->create(), $review);

    expect($review->refresh()->status)->toBe(ReviewStatus::Published);
});
