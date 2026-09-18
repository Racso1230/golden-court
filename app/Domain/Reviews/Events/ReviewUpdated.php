<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Events;

use Illuminate\Foundation\Events\Dispatchable;

final readonly class ReviewUpdated
{
    use Dispatchable;

    public function __construct(
        public int $reviewId,
        public int $courtId,
    ) {}
}
