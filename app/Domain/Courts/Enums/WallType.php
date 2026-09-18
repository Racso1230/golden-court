<?php

declare(strict_types=1);

namespace App\Domain\Courts\Enums;

enum WallType: string
{
    case Panoramic = 'panoramic';
    case Classic = 'classic';

    public function label(): string
    {
        return match ($this) {
            self::Panoramic => 'Panoramic',
            self::Classic => 'Classic',
        };
    }
}
