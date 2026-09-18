<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Actions;

use App\Domain\Reviews\Data\ReplyToReviewData;
use App\Domain\Reviews\Models\ReviewReply;

final class UpdateReviewReplyAction
{
    public function handle(ReviewReply $reply, ReplyToReviewData $data): ReviewReply
    {
        $reply->fill(['body' => $data->body])->save();

        return $reply;
    }
}
