<?php

declare(strict_types=1);

namespace App\Domain\Claims\Exceptions;

use App\Domain\Claims\Enums\ClaimStatus;
use App\Domain\Shared\Exceptions\DomainException;

final class InvalidClaimTransitionException extends DomainException
{
    public static function between(ClaimStatus $from, ClaimStatus $to): self
    {
        return new self(sprintf('A claim cannot move from %s to %s.', $from->value, $to->value));
    }
}
