<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Exceptions;

use App\Domain\Shared\Exceptions\DomainException;

final class InvalidAggregateScoreException extends DomainException
{
    public static function valueOutOfRange(float $value): self
    {
        return new self(sprintf('An aggregate score must be between 0.0 and 5.0, %s given.', $value));
    }

    public static function negativeReviewCount(int $reviewCount): self
    {
        return new self(sprintf('A review count cannot be negative, %d given.', $reviewCount));
    }

    public static function scoreWithoutReviews(float $value): self
    {
        return new self(sprintf('An aggregate score of %s cannot be computed from zero reviews.', $value));
    }
}
