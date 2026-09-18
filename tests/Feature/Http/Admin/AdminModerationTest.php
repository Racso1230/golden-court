<?php

declare(strict_types=1);

use App\Domain\Moderation\Models\ReviewFlag;
use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;

it('lists reviews with open flags, most flagged first', function (): void {
    $twice = Review::factory()->flagged()->create();
    ReviewFlag::factory()->count(2)->for($twice)->create();
    $once = Review::factory()->create();
    ReviewFlag::factory()->for($once)->create();
    $resolved = Review::factory()->create();
    ReviewFlag::factory()->for($resolved)->resolved()->create();

    actingAs(User::factory()->admin()->create())
        ->get(route('admin.flags.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Admin/Flags/Index')
            ->has('reviews.data', 2)
            ->where('reviews.data.0.review.id', $twice->id)
            ->where('reviews.data.0.unresolvedFlagCount', 2)
            ->has('reviews.data.0.flags', 2)
            ->where('reviews.data.1.review.id', $once->id));
});

it('publishes a flagged review and resolves its flags', function (): void {
    $review = Review::factory()->flagged()->create();
    ReviewFlag::factory()->count(3)->for($review)->create();

    actingAs(User::factory()->admin()->create())
        ->from(route('admin.flags.index'))
        ->post(route('admin.reviews.resolve-flags', $review), ['outcome' => 'published'])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.flags.index'));

    expect($review->refresh()->status)->toBe(ReviewStatus::Published)
        ->and($review->flags()->whereNull('resolved_at')->count())->toBe(0);
});

it('removes a flagged review', function (): void {
    $review = Review::factory()->flagged()->create();
    ReviewFlag::factory()->for($review)->create();

    actingAs(User::factory()->admin()->create())
        ->post(route('admin.reviews.resolve-flags', $review), ['outcome' => 'removed'])
        ->assertSessionHasNoErrors();

    expect($review->refresh()->status)->toBe(ReviewStatus::Removed);
});

it('validates the outcome', function (): void {
    $review = Review::factory()->flagged()->create();

    actingAs(User::factory()->admin()->create())
        ->post(route('admin.reviews.resolve-flags', $review), ['outcome' => 'pending'])
        ->assertSessionHasErrors('outcome');
});

it('lists pending reviews and lets an admin publish or remove them', function (): void {
    $admin = User::factory()->admin()->create();
    $first = Review::factory()->pending()->create(['created_at' => '2026-09-01 10:00:00']);
    $second = Review::factory()->pending()->create(['created_at' => '2026-09-02 10:00:00']);
    Review::factory()->create();

    actingAs($admin)
        ->get(route('admin.reviews.pending'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Admin/Reviews/Pending')
            ->has('reviews.data', 2)
            ->where('reviews.data.0.review.id', $first->id));

    actingAs($admin)->post(route('admin.reviews.status', $first), ['outcome' => 'published'])->assertSessionHasNoErrors();
    actingAs($admin)->post(route('admin.reviews.status', $second), ['outcome' => 'removed'])->assertSessionHasNoErrors();

    expect($first->refresh()->status)->toBe(ReviewStatus::Published)
        ->and($second->refresh()->status)->toBe(ReviewStatus::Removed);
});

it('reports an impossible status change as a validation error', function (): void {
    $review = Review::factory()->removed()->create();

    actingAs(User::factory()->admin()->create())
        ->post(route('admin.reviews.status', $review), ['outcome' => 'published'])
        ->assertSessionHasErrors('outcome');
});

it('shows the moderation detail of a review', function (): void {
    $review = Review::factory()->flagged()->create();
    ReviewFlag::factory()->for($review)->create(['details' => 'Copy-pasted from another site.']);

    actingAs(User::factory()->admin()->create())
        ->get(route('admin.reviews.show', $review))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Admin/Reviews/Show')
            ->where('review.review.id', $review->id)
            ->where('review.statusLabel', 'Flagged')
            ->where('review.flags.0.details', 'Copy-pasted from another site.')
            ->where('review.venueSlug', $review->court->venue->slug));
});
