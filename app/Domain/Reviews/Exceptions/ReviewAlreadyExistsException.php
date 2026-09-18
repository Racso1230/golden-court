<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Exceptions;

use App\Domain\Shared\Exceptions\DomainException;
use Throwable;

/**
 * Raised when the database's one-review-per-user-per-court rule fires.
 */
final class ReviewAlreadyExistsException extends DomainException
{
    public static function forUserAndCourt(int $userId, int $courtId, ?Throwable $previous = null): self
    {
        return new self(
            sprintf('User %d has already reviewed court %d.', $userId, $courtId),
            previous: $previous,
        );
    }
}
