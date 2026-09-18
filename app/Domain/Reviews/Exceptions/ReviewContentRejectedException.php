<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Exceptions;

use App\Domain\Shared\Exceptions\DomainException;

final class ReviewContentRejectedException extends DomainException
{
    public static function because(string $reason): self
    {
        return new self($reason);
    }
}
