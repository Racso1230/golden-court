<?php

declare(strict_types=1);

use App\Domain\Reviews\Jobs\RecalculateCourtScore;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;

it('lets the owner delete their review and queues a recalculation', function (): void {
    Queue::fake();
    $review = Review::factory()->for(User::factory()->player())->create();

    actingAs($review->user)
        ->delete(route('reviews.destroy', $review))
        ->assertRedirect(route('courts.show', [$review->court->venue, $review->court]));

    expect(Review::query()->find($review->id))->toBeNull()
        ->and(Review::withTrashed()->find($review->id))->not->toBeNull();

    Queue::assertPushed(RecalculateCourtScore::class, fn (RecalculateCourtScore $job): bool => $job->courtId === $review->court_id);
});

it('lets an admin delete any review', function (): void {
    $review = Review::factory()->create();

    actingAs(User::factory()->admin()->create())
        ->delete(route('reviews.destroy', $review))
        ->assertRedirect(route('courts.show', [$review->court->venue, $review->court]));

    expect(Review::query()->find($review->id))->toBeNull();
});

it('forbids other players from deleting it', function (): void {
    $review = Review::factory()->create();

    actingAs(User::factory()->player()->create())
        ->delete(route('reviews.destroy', $review))
        ->assertForbidden();

    expect(Review::query()->find($review->id))->not->toBeNull();
});

it('returns 404 for a review that is already deleted', function (): void {
    $review = Review::factory()->create();
    $review->delete();

    actingAs(User::factory()->admin()->create())
        ->delete(route('reviews.destroy', $review->id))
        ->assertNotFound();
});
