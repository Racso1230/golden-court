<?php

declare(strict_types=1);

namespace App\Domain\Moderation\Actions;

use App\Domain\Moderation\Exceptions\InvalidFlagOutcomeException;
use App\Domain\Reviews\Actions\ChangeReviewStatusAction;
use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Reviews\Exceptions\ModerationNotPermittedException;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * An admin closes every open flag on a review and either keeps it live or
 * removes it. Dismissing flags on a still-published review leaves its
 * status untouched.
 */
final class ResolveReviewFlagsAction
{
    public function __construct(private readonly ChangeReviewStatusAction $changeStatus) {}

    /**
     * @throws ModerationNotPermittedException when the actor is not an admin
     * @throws InvalidFlagOutcomeException when the outcome is neither published nor removed
     */
    public function handle(Review $review, User $admin, ReviewStatus $outcome): Review
    {
        if (! $admin->canModerate()) {
            throw ModerationNotPermittedException::forActor($admin);
        }

        if (! in_array($outcome, [ReviewStatus::Published, ReviewStatus::Removed], true)) {
            throw InvalidFlagOutcomeException::forStatus($outcome);
        }

        return DB::transaction(function () use ($review, $admin, $outcome): Review {
            $review->flags()->whereNull('resolved_at')->update([
                'resolved_at' => now(),
                'resolved_by_user_id' => $admin->id,
            ]);

            if ($review->status !== $outcome) {
                $this->changeStatus->handle($review, $outcome, $admin);
            }

            return $review;
        });
    }
}
