<?php

declare(strict_types=1);

use App\Domain\Reviews\Actions\ReplyToReviewAction;
use App\Domain\Reviews\Data\ReplyToReviewData;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

function notifyAuthorOf(Review $review): void
{
    app(ReplyToReviewAction::class)->handle(User::factory()->venueOwner()->create(), $review, new ReplyToReviewData('Thanks for coming!'));
}

it('shares the unread count and latest notifications with every page', function (): void {
    $review = Review::factory()->for(User::factory()->player())->create();
    notifyAuthorOf($review);

    actingAs($review->user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('notifications.unreadCount', 1)
            ->has('notifications.items', 1)
            ->where('notifications.items.0.message', fn (mixed $m): bool => is_string($m) && str_contains($m, 'replied to your review'))
            ->where('notifications.items.0.url', route('courts.show', [$review->court->venue, $review->court]))
            ->where('notifications.items.0.readAt', null));
});

it('shares null notifications with guests', function (): void {
    get(route('home'))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->where('notifications', null));
});

it('marks one notification as read', function (): void {
    $review = Review::factory()->for(User::factory()->player())->create();
    notifyAuthorOf($review);
    $notification = $review->user->notifications()->sole();

    actingAs($review->user)
        ->from(route('dashboard'))
        ->post(route('notifications.read', $notification->id))
        ->assertRedirect(route('dashboard'));

    expect($notification->refresh()->read_at)->not->toBeNull();
});

it('refuses to mark someone else\'s notification as read', function (): void {
    $review = Review::factory()->for(User::factory()->player())->create();
    notifyAuthorOf($review);
    $notification = $review->user->notifications()->sole();

    actingAs(User::factory()->player()->create())
        ->post(route('notifications.read', $notification->id))
        ->assertNotFound();

    expect($notification->refresh()->read_at)->toBeNull();
});

it('marks all notifications as read', function (): void {
    $user = User::factory()->player()->create();
    notifyAuthorOf(Review::factory()->for($user)->create());
    notifyAuthorOf(Review::factory()->for($user)->create());

    actingAs($user)->post(route('notifications.read-all'))->assertRedirect();

    expect($user->unreadNotifications()->count())->toBe(0)
        ->and($user->notifications()->count())->toBe(2);
});
