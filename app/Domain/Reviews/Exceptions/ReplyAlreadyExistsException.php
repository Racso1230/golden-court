<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Exceptions;

use App\Domain\Shared\Exceptions\DomainException;
use Throwable;

/**
 * Raised when the database's one-reply-per-review rule fires.
 */
final class ReplyAlreadyExistsException extends DomainException
{
    public static function forReview(int $reviewId, ?Throwable $previous = null): self
    {
        return new self(sprintf('Review %d already has a reply.', $reviewId), previous: $previous);
    }
}
