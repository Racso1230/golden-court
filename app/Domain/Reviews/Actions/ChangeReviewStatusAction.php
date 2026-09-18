<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Actions;

use App\Domain\Moderation\Actions\RecordModerationLogAction;
use App\Domain\Moderation\Contracts\ModerationActor;
use App\Domain\Moderation\Enums\ModerationAction;
use App\Domain\Moderation\Enums\ModerationSubject;
use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Reviews\Events\ReviewStatusChanged;
use App\Domain\Reviews\Exceptions\InvalidStatusTransitionException;
use App\Domain\Reviews\Exceptions\ModerationNotPermittedException;
use App\Domain\Reviews\Models\Review;
use Illuminate\Support\Facades\DB;

final class ChangeReviewStatusAction
{
    public function __construct(private readonly RecordModerationLogAction $recordLog) {}

    /**
     * @throws ModerationNotPermittedException when the actor may not moderate
     * @throws InvalidStatusTransitionException when the status machine forbids the move
     */
    public function handle(Review $review, ReviewStatus $to, ModerationActor $actor): Review
    {
        if (! $actor->canModerate()) {
            throw ModerationNotPermittedException::forActor($actor);
        }

        $from = $review->status;

        if (! $from->canTransitionTo($to)) {
            throw InvalidStatusTransitionException::between($from, $to);
        }

        return DB::transaction(function () use ($review, $from, $to, $actor): Review {
            $review->forceFill(['status' => $to])->save();

            $this->recordLog->handle($actor, ModerationAction::ReviewStatusChanged, ModerationSubject::Review, $review->id, [
                'from' => $from->value,
                'to' => $to->value,
            ]);

            DB::afterCommit(fn () => ReviewStatusChanged::dispatch($review->id, $review->court_id, $from, $to));

            return $review;
        });
    }
}
