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

    /**
     * The moderation state machine: which statuses this one may move to.
     *
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Published, self::Removed],
            self::Published => [self::Flagged],
            self::Flagged => [self::Published, self::Removed],
            self::Removed => [],
        };
    }

    public function canTransitionTo(self $to): bool
    {
        return in_array($to, $this->allowedTransitions(), true);
    }
}
