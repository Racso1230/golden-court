<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Exceptions;

use App\Domain\Moderation\Contracts\ModerationActor;
use App\Domain\Shared\Exceptions\DomainException;

/**
 * Only admins (and the system) moderate. Policies enforce this at the HTTP
 * layer; this is the domain layer refusing to be misused.
 */
final class ModerationNotPermittedException extends DomainException
{
    public static function forActor(ModerationActor $actor): self
    {
        return new self(sprintf('%s is not permitted to moderate.', $actor->moderatorLabel()));
    }
}
