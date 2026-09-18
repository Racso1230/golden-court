<?php

declare(strict_types=1);

namespace App\Domain\Moderation\Contracts;

/**
 * Someone, or something, allowed to take moderation decisions. Admins are
 * actors; so is the system when it escalates a review automatically.
 */
interface ModerationActor
{
    /**
     * The acting user's id, or null for the system.
     */
    public function moderatorId(): ?int;

    public function canModerate(): bool;

    /**
     * A short label for audit logs, e.g. "admin:12" or "system".
     */
    public function moderatorLabel(): string;
}
