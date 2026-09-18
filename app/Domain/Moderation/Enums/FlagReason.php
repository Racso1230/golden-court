<?php

declare(strict_types=1);

namespace App\Domain\Moderation\Enums;

enum FlagReason: string
{
    case Spam = 'spam';
    case Offensive = 'offensive';
    case NotAReview = 'not_a_review';
    case ConflictOfInterest = 'conflict_of_interest';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Spam => 'Spam',
            self::Offensive => 'Offensive',
            self::NotAReview => 'Not a review',
            self::ConflictOfInterest => 'Conflict of interest',
            self::Other => 'Other',
        };
    }
}
