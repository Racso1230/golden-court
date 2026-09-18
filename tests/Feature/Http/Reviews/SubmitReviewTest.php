<?php

declare(strict_types=1);

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Reviews\Jobs\RecalculateCourtScore;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

/**
 * @return array<string, int|string>
 */
function reviewPayload(Court $court): array
{
    return [
        'court_id' => $court->id,
        'glass' => 5,
        'lighting' => 4,
        'turf' => 4,
        'facilities' => 3,
        'body' => 'Fast courts, great glass, showers a bit tired but perfectly usable.',
        'played_on' => '2026-09-10',
    ];
}

it('redirects guests to the login page', function (): void {
    post(route('reviews.store'), reviewPayload(Court::factory()->create()))
        ->assertRedirect(route('login'));
});

it('sends unverified users to verify their email first', function (): void {
    $court = Court::factory()->create();

    actingAs(User::factory()->player()->unverified()->create())
        ->post(route('reviews.store'), reviewPayload($court))
        ->assertRedirect(route('verification.notice'));

    expect(Review::query()->count())->toBe(0);
});

it('creates and auto-publishes a review for a verified player, then queues a recalculation', function (): void {
    Queue::fake();
    $player = User::factory()->player()->create();
    $court = Court::factory()->create();

    actingAs($player)
        ->post(route('reviews.store'), reviewPayload($court))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $review = Review::query()->sole();

    expect($review->user_id)->toBe($player->id)
        ->and($review->court_id)->toBe($court->id)
        ->and($review->status)->toBe(ReviewStatus::Published)
        ->and($review->scores()->toArray())->toBe(['glass' => 5, 'lighting' => 4, 'turf' => 4, 'facilities' => 3])
        ->and($review->played_on?->toDateString())->toBe('2026-09-10');

    Queue::assertPushed(RecalculateCourtScore::class, fn (RecalculateCourtScore $job): bool => $job->courtId === $court->id);
});

it('rejects ratings outside 1..5', function (int $value): void {
    $court = Court::factory()->create();

    actingAs(User::factory()->player()->create())
        ->post(route('reviews.store'), [...reviewPayload($court), 'glass' => $value])
        ->assertSessionHasErrors('glass');

    expect(Review::query()->count())->toBe(0);
})->with([0, 6]);

it('rejects a body shorter than 20 characters', function (): void {
    $court = Court::factory()->create();

    actingAs(User::factory()->player()->create())
        ->post(route('reviews.store'), [...reviewPayload($court), 'body' => 'Too short.'])
        ->assertSessionHasErrors('body');
});

it('rejects a played-on date in the future', function (): void {
    $court = Court::factory()->create();

    actingAs(User::factory()->player()->create())
        ->post(route('reviews.store'), [...reviewPayload($court), 'played_on' => now()->addDay()->toDateString()])
        ->assertSessionHasErrors('played_on');
});

it('rejects an unknown court', function (): void {
    $court = Court::factory()->create();

    actingAs(User::factory()->player()->create())
        ->post(route('reviews.store'), [...reviewPayload($court), 'court_id' => 999_999])
        ->assertSessionHasErrors('court_id');
});

it('forbids venue owners from reviewing', function (): void {
    $court = Court::factory()->create();

    actingAs(User::factory()->venueOwner()->create())
        ->post(route('reviews.store'), reviewPayload($court))
        ->assertForbidden();
});

it('forbids a second review on the same court through the policy', function (): void {
    $review = Review::factory()->for(User::factory()->player())->create();

    actingAs($review->user)
        ->post(route('reviews.store'), reviewPayload($review->court))
        ->assertForbidden();

    expect(Review::query()->count())->toBe(1);
});

it('reports a duplicate the policy cannot see as a friendly validation error, because the database still refuses it', function (): void {
    // A soft-deleted review is invisible to the policy's pre-check but still
    // occupies the (user_id, court_id) unique index.
    $review = Review::factory()->for(User::factory()->player())->create();
    $review->delete();

    actingAs($review->user)
        ->from(route('reviews.create', $review->court))
        ->post(route('reviews.store'), reviewPayload($review->court))
        ->assertRedirect(route('reviews.create', $review->court))
        ->assertSessionHasErrors(['court_id' => 'You have already reviewed this court.']);

    expect(Review::withTrashed()->count())->toBe(1);
});
