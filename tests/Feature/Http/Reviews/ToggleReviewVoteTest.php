<?php

declare(strict_types=1);

use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

it('toggles a helpful vote on and off', function (): void {
    $review = Review::factory()->create();
    $voter = User::factory()->player()->create();

    actingAs($voter)->from(route('home'))->post(route('reviews.vote', $review))->assertRedirect(route('home'));
    expect($review->refresh()->helpful_count)->toBe(1);

    actingAs($voter)->post(route('reviews.vote', $review));
    expect($review->refresh()->helpful_count)->toBe(0);
});

it('forbids the author voting on their own review', function (): void {
    $review = Review::factory()->create();

    actingAs($review->user)->post(route('reviews.vote', $review))->assertForbidden();
});

it('forbids voting on an unpublished review', function (): void {
    $review = Review::factory()->pending()->create();

    actingAs(User::factory()->player()->create())->post(route('reviews.vote', $review))->assertForbidden();
});

it('redirects guests to the login page', function (): void {
    post(route('reviews.vote', Review::factory()->create()))->assertRedirect(route('login'));
});
