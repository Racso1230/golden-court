<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Events carry ids rather than models so they serialise cleanly onto the queue.
 */
final readonly class ReviewSubmitted
{
    use Dispatchable;

    public function __construct(
        public int $reviewId,
        public int $courtId,
    ) {}
}
