<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Events;

use Illuminate\Foundation\Events\Dispatchable;

final readonly class ReviewDeleted
{
    use Dispatchable;

    public function __construct(
        public int $courtId,
    ) {}
}
