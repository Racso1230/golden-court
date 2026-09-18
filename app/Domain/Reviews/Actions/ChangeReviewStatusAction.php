<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Actions;

use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Reviews\Events\ReviewStatusChanged;
use App\Domain\Reviews\Exceptions\InvalidStatusTransitionException;
use App\Domain\Reviews\Exceptions\ModerationNotPermittedException;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;
use Illuminate\Support\Facades\DB;

final class ChangeReviewStatusAction
{
    /**
     * @throws ModerationNotPermittedException when the actor is not an admin
     * @throws InvalidStatusTransitionException when the status machine forbids the move
     */
    public function handle(Review $review, ReviewStatus $to, User $actor): Review
    {
        if (! $actor->isAdmin()) {
            throw ModerationNotPermittedException::forUser($actor);
        }

        $from = $review->status;

        if (! $from->canTransitionTo($to)) {
            throw InvalidStatusTransitionException::between($from, $to);
        }

        return DB::transaction(function () use ($review, $from, $to): Review {
            $review->forceFill(['status' => $to])->save();

            DB::afterCommit(fn () => ReviewStatusChanged::dispatch($review->id, $review->court_id, $from, $to));

            return $review;
        });
    }
}
