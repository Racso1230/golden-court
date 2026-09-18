<?php

declare(strict_types=1);

namespace App\Domain\Venues\ValueObjects;

use App\Domain\Venues\Exceptions\InvalidCoordinatesException;
use JsonSerializable;

/**
 * A WGS84 point on the Earth's surface.
 */
final readonly class Coordinates implements JsonSerializable
{
    public const float MIN_LATITUDE = -90.0;

    public const float MAX_LATITUDE = 90.0;

    public const float MIN_LONGITUDE = -180.0;

    public const float MAX_LONGITUDE = 180.0;

    private const float EARTH_RADIUS_KM = 6371.0;

    private function __construct(
        public float $latitude,
        public float $longitude,
    ) {}

    public static function from(float $latitude, float $longitude): self
    {
        if ($latitude < self::MIN_LATITUDE || $latitude > self::MAX_LATITUDE) {
            throw InvalidCoordinatesException::latitudeOutOfRange($latitude);
        }

        if ($longitude < self::MIN_LONGITUDE || $longitude > self::MAX_LONGITUDE) {
            throw InvalidCoordinatesException::longitudeOutOfRange($longitude);
        }

        return new self($latitude, $longitude);
    }

    /**
     * Great-circle distance using the haversine formula. Good enough for
     * display and tests; geographic queries are done by PostgreSQL.
     */
    public function distanceToInKm(self $other): float
    {
        $latFrom = deg2rad($this->latitude);
        $latTo = deg2rad($other->latitude);
        $deltaLat = deg2rad($other->latitude - $this->latitude);
        $deltaLon = deg2rad($other->longitude - $this->longitude);

        $a = sin($deltaLat / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($deltaLon / 2) ** 2;

        $c = 2 * asin(min(1.0, sqrt($a)));

        return self::EARTH_RADIUS_KM * $c;
    }

    public function equals(self $other): bool
    {
        return $this->latitude === $other->latitude
            && $this->longitude === $other->longitude;
    }

    /**
     * @return array{latitude: float, longitude: float}
     */
    public function jsonSerialize(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
