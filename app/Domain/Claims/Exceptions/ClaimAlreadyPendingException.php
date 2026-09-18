<?php

declare(strict_types=1);

namespace App\Domain\Claims\Exceptions;

use App\Domain\Shared\Exceptions\DomainException;
use Throwable;

/**
 * Raised when the database's one-pending-claim-per-venue index fires.
 */
final class ClaimAlreadyPendingException extends DomainException
{
    public static function forVenue(int $venueId, ?Throwable $previous = null): self
    {
        return new self(sprintf('Venue %d already has a claim awaiting review.', $venueId), previous: $previous);
    }
}
