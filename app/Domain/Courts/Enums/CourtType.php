<?php

declare(strict_types=1);

namespace App\Domain\Courts\Enums;

use App\Domain\Shared\Contracts\HasLabel;

enum CourtType: string implements HasLabel
{
    case Indoor = 'indoor';
    case Outdoor = 'outdoor';
    case Covered = 'covered';

    public function label(): string
    {
        return match ($this) {
            self::Indoor => 'Indoor',
            self::Outdoor => 'Outdoor',
            self::Covered => 'Covered',
        };
    }
}
