<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Actions;

use App\Domain\Reviews\Models\ReviewReply;

final class DeleteReviewReplyAction
{
    public function handle(ReviewReply $reply): void
    {
        $reply->delete();
    }
}
