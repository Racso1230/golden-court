<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Events;

use App\Domain\Reviews\Enums\ReviewStatus;
use Illuminate\Foundation\Events\Dispatchable;

final readonly class ReviewStatusChanged
{
    use Dispatchable;

    public function __construct(
        public int $reviewId,
        public int $courtId,
        public ReviewStatus $from,
        public ReviewStatus $to,
    ) {}
}
