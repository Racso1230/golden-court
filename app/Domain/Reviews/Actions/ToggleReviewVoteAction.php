<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Actions;

use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Models\ReviewVote;
use App\Domain\Users\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Marks a review helpful, or takes the mark back. Returns whether the user
 * has a vote after the call.
 */
final class ToggleReviewVoteAction
{
    public function handle(User $user, Review $review): bool
    {
        return DB::transaction(function () use ($user, $review): bool {
            $existing = ReviewVote::query()
                ->where('review_id', $review->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existing !== null) {
                $existing->delete();
                Review::query()->whereKey($review->id)->toBase()->decrement('helpful_count');

                return false;
            }

            try {
                // A nested transaction is a savepoint, so a failed insert does
                // not poison the outer transaction.
                DB::transaction(function () use ($user, $review): void {
                    ReviewVote::query()->create(['review_id' => $review->id, 'user_id' => $user->id]);
                });
            } catch (UniqueConstraintViolationException) {
                // Two requests raced; the other one already counted the vote.
                return true;
            }

            Review::query()->whereKey($review->id)->toBase()->increment('helpful_count');

            return true;
        });
    }
}
