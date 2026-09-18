<?php

declare(strict_types=1);

use App\Domain\Reviews\Jobs\RecalculateCourtScore;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patch;

/**
 * @return array<string, int|string>
 */
function updatePayload(): array
{
    return [
        'glass' => 2,
        'lighting' => 2,
        'turf' => 3,
        'facilities' => 3,
        'body' => 'Revisited after the refurbishment and it has gone downhill sadly.',
    ];
}

it('lets the owner update their review and queues a recalculation', function (): void {
    Queue::fake();
    $review = Review::factory()->for(User::factory()->player())->create();

    actingAs($review->user)
        ->from(route('dashboard'))
        ->patch(route('reviews.update', $review), updatePayload())
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $review->refresh();

    expect($review->scores()->toArray())->toBe(['glass' => 2, 'lighting' => 2, 'turf' => 3, 'facilities' => 3])
        ->and($review->body)->toBe('Revisited after the refurbishment and it has gone downhill sadly.')
        ->and($review->played_on)->toBeNull();

    Queue::assertPushed(RecalculateCourtScore::class, fn (RecalculateCourtScore $job): bool => $job->courtId === $review->court_id);
});

it('forbids other players from updating it', function (): void {
    $review = Review::factory()->create();

    actingAs(User::factory()->player()->create())
        ->patch(route('reviews.update', $review), updatePayload())
        ->assertForbidden();
});

it('forbids the owner once the review is flagged', function (): void {
    $review = Review::factory()->flagged()->create();

    actingAs($review->user)
        ->patch(route('reviews.update', $review), updatePayload())
        ->assertForbidden();
});

it('validates the payload', function (): void {
    $review = Review::factory()->create();

    actingAs($review->user)
        ->patch(route('reviews.update', $review), [...updatePayload(), 'turf' => 9, 'body' => 'short'])
        ->assertSessionHasErrors(['turf', 'body']);
});

it('redirects guests to the login page', function (): void {
    $review = Review::factory()->create();

    patch(route('reviews.update', $review), updatePayload())->assertRedirect(route('login'));
});
