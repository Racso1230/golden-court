<?php

declare(strict_types=1);

namespace App\Domain\Reviews\ValueObjects;

use JsonSerializable;

/**
 * The four ratings that make up one review of a court.
 */
final readonly class CourtScores implements JsonSerializable
{
    public function __construct(
        public Rating $glass,
        public Rating $lighting,
        public Rating $turf,
        public Rating $facilities,
    ) {}

    public static function fromInts(int $glass, int $lighting, int $turf, int $facilities): self
    {
        return new self(
            Rating::from($glass),
            Rating::from($lighting),
            Rating::from($turf),
            Rating::from($facilities),
        );
    }

    /**
     * Arithmetic mean of the four ratings, rounded to one decimal place.
     */
    public function overall(): float
    {
        $sum = $this->glass->value
            + $this->lighting->value
            + $this->turf->value
            + $this->facilities->value;

        return round($sum / 4, 1);
    }

    /**
     * @return array{glass: int, lighting: int, turf: int, facilities: int}
     */
    public function toArray(): array
    {
        return [
            'glass' => $this->glass->value,
            'lighting' => $this->lighting->value,
            'turf' => $this->turf->value,
            'facilities' => $this->facilities->value,
        ];
    }

    /**
     * @return array{glass: int, lighting: int, turf: int, facilities: int}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
