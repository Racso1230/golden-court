<?php

declare(strict_types=1);

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Actions\SubmitReviewAction;
use App\Domain\Reviews\Data\SubmitReviewData;
use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Reviews\Events\ReviewSubmitted;
use App\Domain\Reviews\Exceptions\ReviewAlreadyExistsException;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Event;

function submitReviewData(Court $court): SubmitReviewData
{
    return new SubmitReviewData(
        courtId: $court->id,
        glass: 5,
        lighting: 4,
        turf: 3,
        facilities: 2,
        body: 'Lovely panoramic glass, lighting could be brighter in the evenings.',
        playedOn: CarbonImmutable::parse('2026-09-01'),
    );
}

it('creates a pending review for the user and court', function (): void {
    Event::fake([ReviewSubmitted::class]);
    $user = User::factory()->player()->create();
    $court = Court::factory()->create();

    $review = app(SubmitReviewAction::class)->handle($user, submitReviewData($court));

    expect($review->exists)->toBeTrue()
        ->and($review->user_id)->toBe($user->id)
        ->and($review->court_id)->toBe($court->id)
        ->and($review->status)->toBe(ReviewStatus::Pending)
        ->and($review->scores()->toArray())->toBe(['glass' => 5, 'lighting' => 4, 'turf' => 3, 'facilities' => 2])
        ->and($review->played_on?->toDateString())->toBe('2026-09-01')
        ->and(Review::query()->count())->toBe(1);
});

it('dispatches ReviewSubmitted with the review and court ids', function (): void {
    Event::fake([ReviewSubmitted::class]);
    $user = User::factory()->player()->create();
    $court = Court::factory()->create();

    $review = app(SubmitReviewAction::class)->handle($user, submitReviewData($court));

    Event::assertDispatched(
        ReviewSubmitted::class,
        fn (ReviewSubmitted $event): bool => $event->reviewId === $review->id && $event->courtId === $court->id,
    );
});

it('translates the database uniqueness violation into a domain exception', function (): void {
    Event::fake([ReviewSubmitted::class]);
    $user = User::factory()->player()->create();
    $court = Court::factory()->create();
    Review::factory()->for($user)->for($court)->create();

    expect(fn () => app(SubmitReviewAction::class)->handle($user, submitReviewData($court)))
        ->toThrow(ReviewAlreadyExistsException::class);

    expect(Review::query()->count())->toBe(1);
    Event::assertNotDispatched(ReviewSubmitted::class);
});
