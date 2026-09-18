<?php

declare(strict_types=1);

namespace App\Domain\Claims\Data;

use Spatie\LaravelData\Data;

final class SubmitVenueClaimData extends Data
{
    public function __construct(
        public string $evidence,
    ) {}
}
