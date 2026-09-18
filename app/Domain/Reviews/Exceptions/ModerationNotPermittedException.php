<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Exceptions;

use App\Domain\Shared\Exceptions\DomainException;
use App\Domain\Users\Models\User;

/**
 * Only admins moderate reviews. The policy enforces this at the HTTP layer;
 * this is the domain layer refusing to be misused.
 */
final class ModerationNotPermittedException extends DomainException
{
    public static function forUser(User $user): self
    {
        return new self(sprintf('User %d is not permitted to moderate reviews.', $user->id));
    }
}
