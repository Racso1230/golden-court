<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Enums;

enum ReviewStatus: string
{
    case Pending = 'pending';
    case Published = 'published';
    case Flagged = 'flagged';
    case Removed = 'removed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Published => 'Published',
            self::Flagged => 'Flagged',
            self::Removed => 'Removed',
        };
    }

    /**
     * Only published reviews are shown to the public and count towards scores.
     */
    public function isVisible(): bool
    {
        return $this === self::Published;
    }
}
