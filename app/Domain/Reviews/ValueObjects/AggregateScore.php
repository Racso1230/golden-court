<?php

declare(strict_types=1);

namespace App\Domain\Reviews\ValueObjects;

use App\Domain\Reviews\Exceptions\InvalidAggregateScoreException;
use JsonSerializable;

/**
 * The computed score for a court or venue, together with the number of
 * reviews it was derived from. The value is always kept to one decimal.
 */
final readonly class AggregateScore implements JsonSerializable
{
    public const float MIN = 0.0;

    public const float MAX = 5.0;

    private function __construct(
        public float $value,
        public int $reviewCount,
    ) {}

    public static function empty(): self
    {
        return new self(self::MIN, 0);
    }

    public static function of(float $value, int $reviewCount): self
    {
        if ($value < self::MIN || $value > self::MAX) {
            throw InvalidAggregateScoreException::valueOutOfRange($value);
        }

        if ($reviewCount < 0) {
            throw InvalidAggregateScoreException::negativeReviewCount($reviewCount);
        }

        if ($reviewCount === 0 && $value !== self::MIN) {
            throw InvalidAggregateScoreException::scoreWithoutReviews($value);
        }

        return new self(round($value, 1), $reviewCount);
    }

    public function hasReviews(): bool
    {
        return $this->reviewCount > 0;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value
            && $this->reviewCount === $other->reviewCount;
    }

    /**
     * @return array{value: float, review_count: int}
     */
    public function jsonSerialize(): array
    {
        return [
            'value' => $this->value,
            'review_count' => $this->reviewCount,
        ];
    }
}
