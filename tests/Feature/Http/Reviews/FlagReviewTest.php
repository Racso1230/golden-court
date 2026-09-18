<?php

declare(strict_types=1);

use App\Domain\Moderation\Enums\FlagReason;
use App\Domain\Moderation\Models\ReviewFlag;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

it('records a flag', function (): void {
    $review = Review::factory()->create();
    $reporter = User::factory()->player()->create();

    actingAs($reporter)
        ->from(route('home'))
        ->post(route('reviews.flags.store', $review), ['reason' => 'spam', 'details' => 'Link to a shop.'])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    $flag = ReviewFlag::query()->sole();

    expect($flag->reason)->toBe(FlagReason::Spam)
        ->and($flag->details)->toBe('Link to a shop.')
        ->and($flag->user_id)->toBe($reporter->id);
});

it('validates the reason', function (): void {
    $review = Review::factory()->create();

    actingAs(User::factory()->player()->create())
        ->post(route('reviews.flags.store', $review), ['reason' => 'boring'])
        ->assertSessionHasErrors('reason');
});

it('forbids a second flag from the same user through the policy', function (): void {
    $flag = ReviewFlag::factory()->create();

    actingAs($flag->user)
        ->post(route('reviews.flags.store', $flag->review), ['reason' => 'spam'])
        ->assertForbidden();
});

it('redirects guests to the login page', function (): void {
    post(route('reviews.flags.store', Review::factory()->create()), ['reason' => 'spam'])->assertRedirect(route('login'));
});

it('forbids the author flagging their own review', function (): void {
    $review = Review::factory()->create();

    actingAs($review->user)->post(route('reviews.flags.store', $review), ['reason' => 'spam'])->assertForbidden();
});
