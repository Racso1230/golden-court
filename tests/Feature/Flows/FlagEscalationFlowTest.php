<?php

declare(strict_types=1);

use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Reviews\Jobs\RecalculateCourtScore;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('hides a review after three flags and lets an admin settle it', function (): void {
    Queue::fake();
    $review = Review::factory()->create();
    $admin = User::factory()->admin()->create();

    foreach (User::factory()->player()->count(3)->create() as $index => $reporter) {
        actingAs($reporter)
            ->post(route('reviews.flags.store', $review), ['reason' => 'spam'])
            ->assertSessionHasNoErrors();

        expect($review->refresh()->status)->toBe($index < 2 ? ReviewStatus::Published : ReviewStatus::Flagged);
    }

    // Hidden from the public court page and queued for a score recalculation.
    get(route('courts.show', [$review->court->venue, $review->court]))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->where('reviews.total', 0));
    Queue::assertPushed(RecalculateCourtScore::class, fn (RecalculateCourtScore $job): bool => $job->courtId === $review->court_id);

    // It shows up in the admin queue, and publishing resolves every flag.
    actingAs($admin)
        ->get(route('admin.flags.index'))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('reviews.data.0.review.id', $review->id)
            ->where('reviews.data.0.unresolvedFlagCount', 3));

    actingAs($admin)
        ->post(route('admin.reviews.resolve-flags', $review), ['outcome' => 'published'])
        ->assertSessionHasNoErrors();

    expect($review->refresh()->status)->toBe(ReviewStatus::Published)
        ->and($review->flags()->whereNull('resolved_at')->count())->toBe(0);

    actingAs($admin)
        ->get(route('admin.flags.index'))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->has('reviews.data', 0));
});
