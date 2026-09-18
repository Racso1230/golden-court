<?php

declare(strict_types=1);

namespace App\Domain\Moderation\Actions;

use App\Domain\Moderation\Data\FlagReviewData;
use App\Domain\Moderation\Exceptions\AlreadyFlaggedException;
use App\Domain\Moderation\Models\ReviewFlag;
use App\Domain\Moderation\SystemActor;
use App\Domain\Reviews\Actions\ChangeReviewStatusAction;
use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Reviews\Models\Review;
use App\Domain\Users\Models\User;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Records a flag and, once enough players agree, hides the review from the
 * public until an admin looks at it. The escalation is taken by the system
 * actor so the audit trail shows it was automatic, not a person.
 */
final class FlagReviewAction
{
    public function __construct(
        private readonly ChangeReviewStatusAction $changeStatus,
        private readonly Repository $config,
    ) {}

    /**
     * @throws AlreadyFlaggedException when the user has already flagged this review
     */
    public function handle(User $user, Review $review, FlagReviewData $data): ReviewFlag
    {
        return DB::transaction(function () use ($user, $review, $data): ReviewFlag {
            $flag = new ReviewFlag;
            $flag->fill([
                'review_id' => $review->id,
                'user_id' => $user->id,
                'reason' => $data->reason,
                'details' => $data->details,
            ]);

            try {
                DB::transaction(fn () => $flag->save());
            } catch (UniqueConstraintViolationException $exception) {
                throw AlreadyFlaggedException::forUserAndReview($user->id, $review->id, $exception);
            }

            if ($review->status === ReviewStatus::Published && $this->unresolvedFlagCount($review) >= $this->threshold()) {
                $this->changeStatus->handle($review, ReviewStatus::Flagged, new SystemActor);
            }

            return $flag;
        });
    }

    private function unresolvedFlagCount(Review $review): int
    {
        return $review->flags()->whereNull('resolved_at')->count();
    }

    private function threshold(): int
    {
        return $this->config->integer('golden_court.moderation.auto_flag_threshold');
    }
}
