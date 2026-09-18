<?php

declare(strict_types=1);

namespace App\Domain\Reviews\Enums;

use App\Domain\Shared\Contracts\HasLabel;

enum ReviewSort: string implements HasLabel
{
    case Recent = 'recent';
    case Helpful = 'helpful';
    case Highest = 'highest';
    case Lowest = 'lowest';

    public function label(): string
    {
        return match ($this) {
            self::Recent => 'Most recent',
            self::Helpful => 'Most helpful',
            self::Highest => 'Highest rated',
            self::Lowest => 'Lowest rated',
        };
    }
}
