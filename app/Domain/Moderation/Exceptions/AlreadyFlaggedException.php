<?php

declare(strict_types=1);

namespace App\Domain\Moderation\Exceptions;

use App\Domain\Shared\Exceptions\DomainException;
use Throwable;

final class AlreadyFlaggedException extends DomainException
{
    public static function forUserAndReview(int $userId, int $reviewId, ?Throwable $previous = null): self
    {
        return new self(sprintf('User %d has already flagged review %d.', $userId, $reviewId), previous: $previous);
    }
}
