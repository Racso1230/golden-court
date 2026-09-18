<?php

declare(strict_types=1);

namespace App\Domain\Reviews\ValueObjects;

use App\Domain\Reviews\Exceptions\InvalidRatingException;
use JsonSerializable;

/**
 * A single 1–5 score a player gives one dimension of a court.
 */
final readonly class Rating implements JsonSerializable
{
    public const int MIN = 1;

    public const int MAX = 5;

    private function __construct(public int $value) {}

    public static function from(int $value): self
    {
        if ($value < self::MIN || $value > self::MAX) {
            throw InvalidRatingException::outOfRange($value);
        }

        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function jsonSerialize(): int
    {
        return $this->value;
    }
}
