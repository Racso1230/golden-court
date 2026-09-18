<?php

declare(strict_types=1);

namespace App\Domain\Courts\Enums;

use App\Domain\Shared\Contracts\HasLabel;

enum Surface: string implements HasLabel
{
    case ArtificialGrass = 'artificial_grass';
    case Carpet = 'carpet';
    case Concrete = 'concrete';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::ArtificialGrass => 'Artificial grass',
            self::Carpet => 'Carpet',
            self::Concrete => 'Concrete',
            self::Other => 'Other',
        };
    }
}
