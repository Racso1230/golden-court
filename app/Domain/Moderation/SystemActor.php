<?php

declare(strict_types=1);

namespace App\Domain\Moderation;

use App\Domain\Moderation\Contracts\ModerationActor;

/**
 * Null-object actor for decisions the application takes on its own, such as
 * escalating a review once enough players have flagged it.
 */
final readonly class SystemActor implements ModerationActor
{
    public function moderatorId(): ?int
    {
        return null;
    }

    public function canModerate(): bool
    {
        return true;
    }

    public function moderatorLabel(): string
    {
        return 'system';
    }
}
