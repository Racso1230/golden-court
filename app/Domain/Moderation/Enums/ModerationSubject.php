<?php

declare(strict_types=1);

namespace App\Domain\Moderation\Enums;

use App\Domain\Shared\Contracts\HasLabel;

enum ModerationSubject: string implements HasLabel
{
    case Review = 'review';
    case Claim = 'claim';

    public function label(): string
    {
        return match ($this) {
            self::Review => 'Review',
            self::Claim => 'Venue claim',
        };
    }
}
