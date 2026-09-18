<?php

declare(strict_types=1);

use App\Domain\Reviews\Actions\DeleteReviewAction;
use App\Domain\Reviews\Events\ReviewDeleted;
use App\Domain\Reviews\Models\Review;
use Illuminate\Support\Facades\Event;

it('soft deletes the review', function (): void {
    Event::fake([ReviewDeleted::class]);
    $review = Review::factory()->create();

    app(DeleteReviewAction::class)->handle($review);

    expect(Review::query()->find($review->id))->toBeNull()
        ->and(Review::withTrashed()->find($review->id))->not->toBeNull();
});

it('dispatches ReviewDeleted with the court id', function (): void {
    Event::fake([ReviewDeleted::class]);
    $review = Review::factory()->create();

    app(DeleteReviewAction::class)->handle($review);

    Event::assertDispatched(ReviewDeleted::class, fn (ReviewDeleted $event): bool => $event->courtId === $review->court_id);
});
