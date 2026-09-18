<?php

declare(strict_types=1);

namespace App\Domain\Claims\Events;

use Illuminate\Foundation\Events\Dispatchable;

final readonly class VenueClaimRejected
{
    use Dispatchable;

    public function __construct(
        public int $claimId,
        public int $venueId,
        public int $userId,
        public ?string $rejectionReason,
    ) {}
}
