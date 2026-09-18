<?php

declare(strict_types=1);

namespace App\Domain\Venues\Exceptions;

use App\Domain\Shared\Exceptions\DomainException;

final class InvalidCoordinatesException extends DomainException
{
    public static function latitudeOutOfRange(float $latitude): self
    {
        return new self(sprintf('Latitude must be between -90 and 90, %s given.', $latitude));
    }

    public static function longitudeOutOfRange(float $longitude): self
    {
        return new self(sprintf('Longitude must be between -180 and 180, %s given.', $longitude));
    }
}
