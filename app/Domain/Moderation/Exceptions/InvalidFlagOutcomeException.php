<?php

declare(strict_types=1);

namespace App\Domain\Moderation\Exceptions;

use App\Domain\Reviews\Enums\ReviewStatus;
use App\Domain\Shared\Exceptions\DomainException;

final class InvalidFlagOutcomeException extends DomainException
{
    public static function forStatus(ReviewStatus $status): self
    {
        return new self(sprintf('Resolving flags must publish or remove the review, not make it %s.', $status->value));
    }
}
