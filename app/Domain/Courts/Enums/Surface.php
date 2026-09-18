<?php

declare(strict_types=1);

namespace App\Domain\Courts\Enums;

enum Surface: string
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
