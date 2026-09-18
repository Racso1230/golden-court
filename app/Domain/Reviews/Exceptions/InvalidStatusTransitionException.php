<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Exceptions;

use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Shared\Exceptions\DomainException;

final class InvalidStatusTransitionException extends DomainException
{
    public static function between(ReviewStatus $from, ReviewStatus $to): self
    {
        return new self(sprintf('A review cannot move from %s to %s.', $from->value, $to->value));
    }
}
