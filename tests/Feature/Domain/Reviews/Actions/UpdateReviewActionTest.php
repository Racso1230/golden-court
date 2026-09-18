<?php

declare(strict_types=1);

use App\Domain\Reviews\Actions\UpdateReviewAction;
use App\Domain\Reviews\Data\UpdateReviewData;
use App\Domain\Reviews\Events\ReviewUpdated;
use App\Domain\Reviews\Models\Review;
use Illuminate\Support\Facades\Event;

it('updates the ratings, body and played-on date', function (): void {
    Event::fake([ReviewUpdated::class]);
    $review = Review::factory()->create(['glass_rating' => 1, 'body' => str_repeat('old ', 10)]);

    $updated = app(UpdateReviewAction::class)->handle($review, new UpdateReviewData(
        glass: 5,
        lighting: 5,
        turf: 4,
        facilities: 4,
        body: 'Much better since the resurfacing, well worth a visit.',
    ));

    $fresh = $updated->refresh();

    expect($fresh->scores()->toArray())->toBe(['glass' => 5, 'lighting' => 5, 'turf' => 4, 'facilities' => 4])
        ->and($fresh->body)->toBe('Much better since the resurfacing, well worth a visit.')
        ->and($fresh->played_on)->toBeNull();
});

it('dispatches ReviewUpdated', function (): void {
    Event::fake([ReviewUpdated::class]);
    $review = Review::factory()->create();

    app(UpdateReviewAction::class)->handle($review, new UpdateReviewData(
        glass: 3,
        lighting: 3,
        turf: 3,
        facilities: 3,
        body: 'Average all round, nothing to write home about.',
    ));

    Event::assertDispatched(
        ReviewUpdated::class,
        fn (ReviewUpdated $event): bool => $event->reviewId === $review->id && $event->courtId === $review->court_id,
    );
});
