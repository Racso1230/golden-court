<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Exceptions;

use App\Domain\Shared\Exceptions\DomainException;

final class InvalidRatingException extends DomainException
{
    public static function outOfRange(int $value): self
    {
        return new self(sprintf('A rating must be between 1 and 5, %d given.', $value));
    }
}
