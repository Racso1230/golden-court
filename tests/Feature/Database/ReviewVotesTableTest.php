<?php

declare(strict_types=1);

use App\Domain\Reviews\Models\ReviewVote;
use Illuminate\Support\Facades\DB;

it('rejects a second helpful vote from the same user on the same review', function (): void {
    $vote = ReviewVote::factory()->create();

    expectUniqueViolation(fn () => ReviewVote::factory()->for($vote->review)->for($vote->user)->create());
});

it('allows different users to vote on the same review', function (): void {
    $vote = ReviewVote::factory()->create();
    ReviewVote::factory()->for($vote->review)->create();

    expect(ReviewVote::query()->where('review_id', $vote->review_id)->count())->toBe(2);
});

it('cascades deletion from the user', function (): void {
    $vote = ReviewVote::factory()->create();

    DB::table('users')->where('id', $vote->user_id)->delete();

    expect(DB::table('review_votes')->where('id', $vote->id)->exists())->toBeFalse();
});
