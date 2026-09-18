<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Actions;

use App\Domain\Reviews\Data\ReplyToReviewData;
use App\Domain\Reviews\Events\ReviewReplied;
use App\Domain\Reviews\Exceptions\ReplyAlreadyExistsException;
use App\Domain\Reviews\Models\Review;
use App\Domain\Reviews\Models\ReviewReply;
use App\Domain\Users\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

final class ReplyToReviewAction
{
    /**
     * @throws ReplyAlreadyExistsException when the review already has its one reply
     */
    public function handle(User $owner, Review $review, ReplyToReviewData $data): ReviewReply
    {
        return DB::transaction(function () use ($owner, $review, $data): ReviewReply {
            $reply = new ReviewReply;
            $reply->fill([
                'review_id' => $review->id,
                'user_id' => $owner->id,
                'body' => $data->body,
            ]);

            try {
                DB::transaction(fn () => $reply->save());
            } catch (UniqueConstraintViolationException $exception) {
                throw ReplyAlreadyExistsException::forReview($review->id, $exception);
            }

            DB::afterCommit(fn () => ReviewReplied::dispatch($review->id, $reply->id));

            return $reply;
        });
    }
}
