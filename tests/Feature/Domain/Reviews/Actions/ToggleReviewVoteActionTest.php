<?php

declare(strict_types=1);

use App\Domain\Reviews\Actions\ToggleReviewVoteAction;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Models\ReviewVote;
use App\Domain\Users\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;

it('records a helpful vote and bumps the count', function (): void {
    $review = Review::factory()->create();
    $voter = User::factory()->player()->create();

    $voted = app(ToggleReviewVoteAction::class)->handle($voter, $review);

    expect($voted)->toBeTrue()
        ->and(ReviewVote::query()->where('review_id', $review->id)->where('user_id', $voter->id)->exists())->toBeTrue()
        ->and($review->refresh()->helpful_count)->toBe(1);
});

it('takes the vote back on a second call', function (): void {
    $review = Review::factory()->create();
    $voter = User::factory()->player()->create();
    $action = app(ToggleReviewVoteAction::class);

    $action->handle($voter, $review);
    $voted = $action->handle($voter, $review);

    expect($voted)->toBeFalse()
        ->and(ReviewVote::query()->count())->toBe(0)
        ->and($review->refresh()->helpful_count)->toBe(0);
});

it('keeps the count consistent under repeated toggles by several users', function (): void {
    $review = Review::factory()->create();
    $voters = User::factory()->player()->count(3)->create();
    $action = app(ToggleReviewVoteAction::class);

    foreach ($voters as $voter) {
        $action->handle($voter, $review);
    }
    $action->handle($voters[0], $review);
    $action->handle($voters[0], $review);
    $action->handle($voters[1], $review);

    expect($review->refresh()->helpful_count)->toBe(2)
        ->and(ReviewVote::query()->where('review_id', $review->id)->count())->toBe(2);
});

it('treats a lost insert race as already voted', function (): void {
    $review = Review::factory()->create();
    $voter = User::factory()->player()->create();

    // A concurrent request cannot be reproduced inside the test transaction,
    // so simulate the database rejecting our insert after the lookup missed.
    ReviewVote::creating(function (): void {
        throw new UniqueConstraintViolationException('pgsql', 'insert into review_votes', [], new PDOException('duplicate key value violates unique constraint'));
    });

    $voted = app(ToggleReviewVoteAction::class)->handle($voter, $review);

    expect($voted)->toBeTrue()
        ->and($review->refresh()->helpful_count)->toBe(0);
});
