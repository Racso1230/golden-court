<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Events;

use Illuminate\Foundation\Events\Dispatchable;

final readonly class ReviewReplied
{
    use Dispatchable;

    public function __construct(
        public int $reviewId,
        public int $replyId,
    ) {}
}
