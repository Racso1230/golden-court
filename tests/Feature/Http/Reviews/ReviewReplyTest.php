<?php

declare(strict_types=1);

use App\Domain\Courts\Models\Court;
use App\Domain\Reviews\Data\ReviewReplyData;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Models\ReviewReply;
use App\Domain\Users\Models\User;
use App\Domain\Venues\Models\Venue;

use function Pest\Laravel\actingAs;

function reviewOwnedBy(User $owner): Review
{
    return Review::factory()->for(Court::factory()->for(Venue::factory()->claimedBy($owner)))->create();
}

it('lets the venue owner post, edit and delete their reply', function (): void {
    $owner = User::factory()->venueOwner()->create();
    $review = reviewOwnedBy($owner);

    actingAs($owner)
        ->from(route('home'))
        ->post(route('reviews.reply.store', $review), ['body' => 'Thanks, glad you enjoyed it.'])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    expect($review->refresh()->reply?->body)->toBe('Thanks, glad you enjoyed it.');

    actingAs($owner)
        ->patch(route('reviews.reply.update', $review), ['body' => 'Thanks! Lights are being upgraded next month.'])
        ->assertSessionHasNoErrors();

    expect($review->refresh()->reply?->body)->toBe('Thanks! Lights are being upgraded next month.');

    actingAs($owner)->delete(route('reviews.reply.destroy', $review))->assertRedirect();

    expect(ReviewReply::query()->count())->toBe(0);
});

it('forbids the owner of a different venue', function (): void {
    $review = reviewOwnedBy(User::factory()->venueOwner()->create());

    actingAs(User::factory()->venueOwner()->create())
        ->post(route('reviews.reply.store', $review), ['body' => 'Not my venue.'])
        ->assertForbidden();
});

it('forbids players', function (): void {
    $review = reviewOwnedBy(User::factory()->venueOwner()->create());

    actingAs(User::factory()->player()->create())
        ->post(route('reviews.reply.store', $review), ['body' => 'I am not the owner.'])
        ->assertForbidden();
});

it('lets an admin reply', function (): void {
    $review = reviewOwnedBy(User::factory()->venueOwner()->create());

    actingAs(User::factory()->admin()->create())
        ->post(route('reviews.reply.store', $review), ['body' => 'Admin note.'])
        ->assertSessionHasNoErrors();

    expect($review->refresh()->reply)->not->toBeNull();
});

it('reports a second reply as a validation error', function (): void {
    $owner = User::factory()->venueOwner()->create();
    $review = reviewOwnedBy($owner);
    ReviewReply::factory()->for($review)->for($owner)->create();

    actingAs($owner)
        ->post(route('reviews.reply.store', $review), ['body' => 'Second reply.'])
        ->assertSessionHasErrors('body');
});

it('404s when editing a reply that does not exist', function (): void {
    $owner = User::factory()->venueOwner()->create();

    actingAs($owner)
        ->patch(route('reviews.reply.update', reviewOwnedBy($owner)), ['body' => 'Nothing here.'])
        ->assertNotFound();
});

it('validates the body length', function (): void {
    $owner = User::factory()->venueOwner()->create();

    actingAs($owner)
        ->post(route('reviews.reply.store', reviewOwnedBy($owner)), ['body' => str_repeat('x', 1001)])
        ->assertSessionHasErrors('body');
});

it('does not present an admin reply as the owner\'s', function (): void {
    $review = reviewOwnedBy(User::factory()->venueOwner()->create());

    actingAs(User::factory()->admin()->create())
        ->post(route('reviews.reply.store', $review), ['body' => 'Admin note.']);

    $reply = $review->refresh()->reply ?? throw new RuntimeException('The admin reply was not saved.');

    expect(ReviewReplyData::fromModel($reply)->fromOwner)->toBeFalse();
});
