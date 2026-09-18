<?php

declare(strict_types=1);

namespace App\Domain\Claims\Exceptions;

use App\Domain\Shared\Exceptions\DomainException;

final class VenueAlreadyClaimedException extends DomainException
{
    public static function forVenue(int $venueId): self
    {
        return new self(sprintf('Venue %d already has an owner.', $venueId));
    }
}
